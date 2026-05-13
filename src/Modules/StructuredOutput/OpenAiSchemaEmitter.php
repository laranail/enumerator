<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Modules\StructuredOutput;

use ReflectionEnum;
use Simtabi\Laranail\Enumerator\Support\IsEnumeratorClass;

/**
 * OpenAI structured-output schema emitter.
 *
 * Converts an enumerator class into two JSON-schema fragments OpenAI's
 * API consumes:
 *
 *   - `asResponseFormat()`  — for the `response_format` field of
 *                              `chat.completions` (json_schema strict mode)
 *   - `asToolParameter()`   — for the `parameters.properties.{field}`
 *                              entry of a function/tool definition
 *
 * Pure schema emission — no `openai-php/client` dependency at the file
 * level. Consumers paste the output into their own client calls.
 */
final class OpenAiSchemaEmitter
{
    /**
     * Emit the wrapper format for `response_format`:
     *
     *     {
     *       "type": "json_schema",
     *       "json_schema": {
     *         "name": "UserStatusEnum",
     *         "strict": true,
     *         "schema": { "type": "string", "enum": [...] }
     *       }
     *     }
     *
     * @param  class-string  $enumClass
     * @return array<string, mixed>
     */
    public function asResponseFormat(string $enumClass): array
    {
        $name = $this->shortName($enumClass);

        return [
            'type' => 'json_schema',
            'json_schema' => [
                'name' => $name,
                'strict' => true,
                'schema' => $this->asToolParameter($enumClass),
            ],
        ];
    }

    /**
     * Emit a tool parameter schema for OpenAI function-calling:
     *
     *     { "type": "string", "enum": [...], "description": "..." }
     *
     * @param  class-string  $enumClass
     * @return array{type: string, enum: list<int|string>, description?: string}
     */
    public function asToolParameter(string $enumClass): array
    {
        if (! IsEnumeratorClass::check($enumClass)) {
            return ['type' => 'string', 'enum' => []];
        }

        $type = $this->typeOf($enumClass);
        $values = [];
        foreach (IsEnumeratorClass::casesOf($enumClass) as $case) {
            $values[] = IsEnumeratorClass::valueOf($case);
        }

        $out = ['type' => $type, 'enum' => $values];
        $description = $this->classDescription($enumClass);
        if ($description !== '') {
            $out['description'] = $description;
        }

        return $out;
    }

    /**
     * @param  class-string  $enumClass
     */
    private function typeOf(string $enumClass): string
    {
        if (! enum_exists($enumClass)) {
            return 'string';
        }
        $reflection = new ReflectionEnum($enumClass);
        if (! $reflection->isBacked()) {
            return 'string';
        }
        $backing = (string) $reflection->getBackingType();

        return $backing === 'int' ? 'integer' : 'string';
    }

    /**
     * @param  class-string  $enumClass
     */
    private function shortName(string $enumClass): string
    {
        $reflection = enum_exists($enumClass)
            ? new ReflectionEnum($enumClass)
            : new \ReflectionClass($enumClass);

        return $reflection->getShortName();
    }

    /**
     * @param  class-string  $enumClass
     */
    private function classDescription(string $enumClass): string
    {
        if (! method_exists($enumClass, 'classDescription')) {
            return '';
        }
        try {
            $value = $enumClass::classDescription();
        } catch (\Throwable) {
            return '';
        }

        return is_string($value) ? $value : '';
    }
}
