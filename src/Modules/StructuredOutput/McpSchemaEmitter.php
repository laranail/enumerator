<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Modules\StructuredOutput;

use ReflectionEnum;
use Simtabi\Laranail\Enumerator\Support\IsEnumeratorClass;

/**
 * Model Context Protocol (MCP) schema emitter.
 *
 * Converts an enumerator class into a JSON-schema fragment usable in
 * MCP tool definitions (`tools.inputSchema.properties.{field}`) or MCP
 * resource templates.
 *
 * MCP uses standard JSON Schema, so the shape mirrors the Anthropic
 * emitter — but kept distinct so the API can evolve independently.
 */
final class McpSchemaEmitter
{
    /**
     * Emit a tool input property schema for an MCP tool definition.
     *
     * @param  class-string  $enumClass
     * @return array{type: string, enum: list<int|string>, description?: string, title?: string}
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
            'title' => $this->shortName($enumClass),
        ];
        $description = $this->classDescription($enumClass);
        if ($description !== '') {
            $out['description'] = $description;
        }

        return $out;
    }

    /**
     * Emit a complete MCP resource-template schema fragment (suitable
     * for `resources/templates/list` responses where the URI template
     * accepts an enum-typed parameter).
     *
     * @param  class-string  $enumClass
     * @return array<string, mixed>
     */
    public function asResourceParameter(string $enumClass): array
    {
        return $this->asToolInputProperty($enumClass);
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
