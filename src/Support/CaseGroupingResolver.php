<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Support;

use Closure;
use Illuminate\Support\Str;
use UnitEnum;

/**
 * Shared case-grouping resolver. Both `Support\BladeViewHelpers`
 * and `Blade\Components\Concerns\GroupsCases` delegate here so the five
 * grouping strategies live in one place.
 *
 * Strategies (selected by `$groupsBy`):
 *
 *   • null                       — no grouping (returns null)
 *   • array<string, cases[]>     — explicit pre-built map (returned as-is)
 *   • Closure                    — fn(case): string → group key
 *   • 'groups'                   — uses `static::groups()` if the enum has HasGrouping
 *   • 'meta:<key>'               — groups by the case's `meta(<key>)`
 *   • 'attribute:<accessor>'     — groups by `$case->{$accessor}()`
 *
 * `$groupLabels` optionally remaps group keys to human labels.
 */
final class CaseGroupingResolver
{
    /**
     * @param  array<int, UnitEnum|object>  $cases
     * @param  string|Closure|array<string, array<int, mixed>>|null  $groupsBy
     * @param  array<string, string>  $groupLabels
     * @return array<string, array<int, UnitEnum|object>>|null
     */
    public static function resolve(
        array $cases,
        string|Closure|array|null $groupsBy,
        array $groupLabels = [],
    ): ?array {
        if ($groupsBy === null) {
            return null;
        }

        if (is_array($groupsBy)) {
            return self::relabel($groupsBy, $groupLabels);
        }

        if ($groupsBy instanceof Closure) {
            return self::relabel(self::groupBy($cases, $groupsBy), $groupLabels);
        }

        if ($groupsBy === 'groups') {
            $enumClass = $cases !== [] ? $cases[0]::class : null;
            if ($enumClass !== null && method_exists($enumClass, 'groups')) {
                /** @var array<string, array<int, UnitEnum>> $groups */
                $groups = $enumClass::groups();
                if ($groups !== []) {
                    return self::relabel($groups, $groupLabels);
                }
            }

            return null;
        }

        if (Str::startsWith($groupsBy, 'meta:')) {
            $key = substr($groupsBy, 5);

            return self::relabel(
                self::groupBy($cases, static fn (object $c): string => method_exists($c, 'meta')
                    ? (string) ($c->meta($key) ?? '')
                    : ''),
                $groupLabels,
            );
        }

        if (Str::startsWith($groupsBy, 'attribute:')) {
            $key = substr($groupsBy, 10);

            return self::relabel(
                self::groupBy($cases, static fn (object $c): string => method_exists($c, $key)
                    ? (string) ($c->{$key}() ?? '')
                    : ''),
                $groupLabels,
            );
        }

        return null;
    }

    /**
     * @param  array<int, UnitEnum|object>  $cases
     * @return array<string, array<int, UnitEnum|object>>
     */
    private static function groupBy(array $cases, Closure $keyOf): array
    {
        $out = [];
        foreach ($cases as $case) {
            $out[(string) $keyOf($case)][] = $case;
        }

        return $out;
    }

    /**
     * @param  array<string, array<int, UnitEnum|object>>  $groups
     * @param  array<string, string>  $labels
     * @return array<string, array<int, UnitEnum|object>>
     */
    private static function relabel(array $groups, array $labels): array
    {
        if ($labels === []) {
            return $groups;
        }
        $out = [];
        foreach ($groups as $key => $cases) {
            $out[$labels[$key] ?? $key] = $cases;
        }

        return $out;
    }
}
