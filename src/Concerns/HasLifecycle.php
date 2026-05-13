<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Concerns;

use Simtabi\Laranail\Enumerator\Support\AttributesCache;
use UnitEnum;

/**
 * Linear lifecycle helpers (next / previous / isFirst / isLast).
 *
 * Order is taken from `HasOrder` if mixed in, else from the declaration order
 * of cases. Tied orders break by declaration index.
 */
trait HasLifecycle
{
    public function next(): ?static
    {
        $ordered = static::orderedCases();
        $index = self::indexOf($ordered, $this);

        /** @var static|null */
        return $ordered[$index + 1] ?? null;
    }

    public function previous(): ?static
    {
        $ordered = static::orderedCases();
        $index = self::indexOf($ordered, $this);

        /** @var static|null */
        return $ordered[$index - 1] ?? null;
    }

    public function isFirst(): bool
    {
        return static::orderedCases()[0] === $this;
    }

    public function isLast(): bool
    {
        $ordered = static::orderedCases();

        return $ordered[array_key_last($ordered)] === $this;
    }

    /**
     * @return array<int, static>
     */
    protected static function orderedCases(): array
    {
        $cases = static::cases();
        usort(
            $cases,
            static fn (UnitEnum $a, UnitEnum $b): int => (AttributesCache::for($a)->order ?? PHP_INT_MAX)
                <=> (AttributesCache::for($b)->order ?? PHP_INT_MAX),
        );

        /** @var array<int, static> */
        return array_values($cases);
    }

    /**
     * @param  array<int, static>  $list
     */
    private static function indexOf(array $list, UnitEnum $needle): int
    {
        foreach ($list as $i => $case) {
            if ($case === $needle) {
                return $i;
            }
        }

        return -1;
    }
}
