<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Modules\StructuredOutput;

use ReflectionEnum;
use Simtabi\Laranail\Enumerator\Support\IsEnumeratorClass;

/**
 * Anthropic tool-use schema emitter.
 *
 * Converts an enumerator class into a JSON-schema fragment usable in
 * Anthropic's `tools.input_schema.properties.{field}` definition.
 * Anthropic's tool-use schema follows JSON Schema Draft 2020-12, so
 * the shape is the standard `{ type, enum, description }` triple.
 *
 * Pure schema emission — no `anthropic-ai/sdk` dependency.
 */
final class AnthropicSchemaEmitter
{
    /**
     * Emit a tool-input-property schema fragment.
     *
     * @param  class-string  $enumClass
     * @return array{type: string, enum: list<int|string>, description?: string}
     */
    public function asToolInputProperty(string $enumClass): array
    {
        if (! IsEnumeratorClass::check($enumClass)) {
            return ['type' => 'string', 'enum' => []];
        }

        $values = [];
        foreach (IsEnumeratorClass::casesOf($enumClass) as $case) {
            $values[] = IsEnumeratorClass::valueOf($case);
        }

        $out = [
            'type' => $this->typeOf($enumClass),
            'enum' => $values,
        ];
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
