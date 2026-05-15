<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Blade\Components\Concerns;

use Closure;
use Simtabi\Laranail\Enumerator\Support\CaseGroupingResolver;
use UnitEnum;

/**
 * Thin trait wrapper around `Support\CaseGroupingResolver` (— the
 * five grouping strategies live in one place now).
 *
 * Strategies, in order of precedence:
 *   1. null                 — no grouping
 *   2. array<string, list>  — explicit map (returned as-is)
 *   3. Closure              — `fn(case): string` returns the group key
 *   4. string `groups`      — use `static::groups()` from `HasGrouping`
 *   5. string `meta:<key>`  — group by `$case->meta($key)`
 *   6. string `attribute:<a>` — group by `$case->{$a}()` (color, icon, order…)
 */
trait GroupsCases
{
    /**
     * @param  array<int, UnitEnum|object>  $cases
     * @param  string|Closure|array<string, array<int, UnitEnum|object>>|null  $groupsBy
     * @param  array<string, string>  $groupLabels
     * @return array<string, array<int, UnitEnum|object>>|null
     */
    protected function buildGroups(
        array $cases,
        string|Closure|array|null $groupsBy,
        array $groupLabels = [],
    ): ?array {
        return CaseGroupingResolver::resolve($cases, $groupsBy, $groupLabels);
    }
}
