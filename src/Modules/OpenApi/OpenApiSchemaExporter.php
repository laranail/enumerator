<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Modules\OpenApi;

use BackedEnum;
use ReflectionEnum;
use Simtabi\Laranail\Enumerator\AbstractEnumeratorClass;
use Simtabi\Laranail\Enumerator\Support\IsEnumeratorClass;

/**
 * OpenAPI 3.1 component schema emitter.
 *
 * Given an enumerator class, produces the JSON shape required for an
 * OpenAPI `components.schemas.{Name}` entry:
 *
 *     {
 *       "type": "string",
 *       "enum": ["active", "inactive", "banned"],
 *       "x-enum-varnames": ["Active", "Inactive", "Banned"],
 *       "x-enum-descriptions": ["…", "…", "…"]
 *     }
 *
 * The `x-enum-*` vendor extensions come from common OpenAPI codegen
 * conventions (used by openapi-generator, swagger-codegen, etc.) so
 * generated client SDKs can produce idiomatic enum types.
 */
final class OpenApiSchemaExporter
{
    /**
     * Emit an OpenAPI 3.1 schema fragment for the given enum class.
     *
     * @param  class-string  $enumClass
     * @return array{type: string, enum: list<int|string>, 'x-enum-varnames': list<string>, 'x-enum-descriptions'?: list<string>}
     */
    public function export(string $enumClass): array
    {
        if (! IsEnumeratorClass::check($enumClass)) {
            return [
                'type' => 'string',
                'enum' => [],
                'x-enum-varnames' => [],
            ];
        }

        $values = [];
        $names = [];
        $descriptions = [];
        $hasAnyDescription = false;

        foreach (IsEnumeratorClass::casesOf($enumClass) as $case) {
            $values[] = IsEnumeratorClass::valueOf($case);
            $names[] = $this->nameOf($case);
            $description = method_exists($case, 'description') ? (string) $case->description() : '';
            if ($description !== '') {
                $hasAnyDescription = true;
            }
            $descriptions[] = $description;
        }

        $schema = [
            'type' => $this->typeOf($enumClass),
            'enum' => $values,
            'x-enum-varnames' => $names,
        ];

        if ($hasAnyDescription) {
            $schema['x-enum-descriptions'] = $descriptions;
        }

        return $schema;
    }

    /**
     * OpenAPI `type` for the enum's backing. Backed `int` enums get
     * `integer`; everything else (string-backed, pure, class-const) gets
     * `string`.
     *
     * @param  class-string  $enumClass
     */
    private function typeOf(string $enumClass): string
    {
        if (! enum_exists($enumClass)) {
            return 'string';  // AbstractEnumeratorClass — constants are mixed-type but we render as string
        }

        $reflection = new ReflectionEnum($enumClass);
        if (! $reflection->isBacked()) {
            return 'string';
        }
        $backing = (string) $reflection->getBackingType();

        return $backing === 'int' ? 'integer' : 'string';
    }

    private function nameOf(object $case): string
    {
        if ($case instanceof BackedEnum || $case instanceof \UnitEnum) {
            return $case->name;
        }
        if ($case instanceof AbstractEnumeratorClass && method_exists($case, 'getKey')) {
            return (string) $case->getKey();
        }

        return $case::class;
    }
}
