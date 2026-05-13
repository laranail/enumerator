<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Modules\GraphQL;

use ReflectionEnum;
use Simtabi\Laranail\Enumerator\AbstractEnumeratorClass;
use Simtabi\Laranail\Enumerator\Support\IsEnumeratorClass;

/**
 * Framework-agnostic GraphQL schema fragment emitter.
 *
 * Distinct from `Modules/Lighthouse/EnumScalar` (Step 26), which targets
 * Lighthouse's runtime resolver pipeline. This exporter emits a portable
 * `.graphql` schema fragment usable by any GraphQL parser (Mercurius,
 * API Platform, Apollo, raw webonyx/graphql-php, Hasura remote schema).
 *
 * Output shape:
 *
 *     """ User account lifecycle states. """
 *     enum UserStatusEnum {
 *       """ Account is active and signed-in. """
 *       ACTIVE
 *       INACTIVE
 *       BANNED
 *     }
 *
 * Per-case descriptions come from `#[Description]` attributes on cases.
 * The class-level description (if any) is rendered above the `enum`
 * keyword as a GraphQL block-string.
 */
final class SchemaExporter
{
    /**
     * Emit a portable `.graphql` schema fragment for the given enum.
     *
     * @param  class-string  $enumClass
     */
    public function export(string $enumClass): string
    {
        if (! IsEnumeratorClass::check($enumClass)) {
            return '';
        }

        $name = $this->shortName($enumClass);
        $classDescription = $this->classDescription($enumClass);

        $lines = [];
        if ($classDescription !== '') {
            $lines[] = '"""';
            $lines[] = $classDescription;
            $lines[] = '"""';
        }
        $lines[] = "enum {$name} {";

        foreach (IsEnumeratorClass::casesOf($enumClass) as $case) {
            $caseName = $this->caseName($case);
            $description = method_exists($case, 'description') ? (string) $case->description() : '';
            if ($description !== '') {
                $lines[] = '  """ ' . $this->escapeBlockString($description) . ' """';
            }
            $lines[] = '  ' . $caseName;
        }
        $lines[] = '}';

        return implode("\n", $lines) . "\n";
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

    private function caseName(object $case): string
    {
        if ($case instanceof \UnitEnum) {
            return $case->name;
        }
        if ($case instanceof AbstractEnumeratorClass && method_exists($case, 'getKey')) {
            return (string) $case->getKey();
        }

        return $case::class;
    }

    /**
     * GraphQL block strings (`""" ... """`) can't contain `"""` literally.
     * Escape any occurrence by inserting a zero-width backslash.
     */
    private function escapeBlockString(string $value): string
    {
        return str_replace('"""', '\"\"\"', $value);
    }
}
