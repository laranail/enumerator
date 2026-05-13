<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Support;

use BackedEnum;
use Closure;
use UnitEnum;

/**
 * Tiny helpers callable from anonymous Blade variants when they receive
 * grouping/option flags via raw attributes (i.e. not through the canonical
 * class component). Keeps the framework views self-sufficient.
 */
final class BladeViewHelpers
{
    /**
     * Build an `[optgroup-label => cases[]]` map from the same DSL accepted
     * by the canonical Select/Dropdown components:
     *
     *   • null                       — no grouping (returns null)
     *   • 'groups'                   — uses static::groups() if present
     *   • 'meta:<key>'               — groups by case meta key
     *   • 'attribute:<accessor>'     — groups by case method (color/icon/order)
     *   • Closure                    — fn(case): string → group key
     *   • array<string, cases[]>     — explicit map (returned as-is)
     *
     * @param  array<int, UnitEnum|object>  $cases
     * @param  string|Closure|array<string, array<int, mixed>>|null  $groupsBy
     * @param  array<string, string>  $groupLabels
     * @return array<string, array<int, UnitEnum|object>>|null
     */
    public static function buildGroups(
        array $cases,
        string|Closure|array|null $groupsBy,
        array $groupLabels = [],
    ): ?array {
        // delegate to the shared resolver. Old inline copy removed.
        return CaseGroupingResolver::resolve($cases, $groupsBy, $groupLabels);
    }

    /**
     * Normalize a selected scalar/enum/iterable into a comparable value
     * (or list of values, for multi-select). Public so views can use it.
     */
    public static function normalizeSelected(mixed $selected, bool $multiple = false): mixed
    {
        if ($selected === null) {
            return null;
        }
        if ($multiple && is_iterable($selected) && ! ($selected instanceof UnitEnum)) {
            $out = [];
            foreach ($selected as $item) {
                $out[] = self::toScalar($item);
            }

            return $out;
        }

        return self::toScalar($selected);
    }

    private static function toScalar(mixed $value): string|int
    {
        if ($value instanceof BackedEnum) {
            return $value->value;
        }
        if ($value instanceof UnitEnum) {
            return $value->name;
        }
        if (is_object($value) && method_exists($value, 'getValue')) {
            return (string) $value->getValue();
        }

        return is_scalar($value) ? $value : (string) $value;
    }
}
