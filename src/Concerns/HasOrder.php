<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Concerns;

use Simtabi\Laranail\Enumerator\Support\AttributesCache;
use Simtabi\Laranail\Enumerator\Support\CasesCollection;
use UnitEnum;

/**
 * Attribute-driven ordering. Reads `#[Order]` from each case. Provides
 * instance-level comparison (`compareTo`, `isHigherThan`, `isLowerThan`) and
 * static collection helpers (`sortedByOrder`, `sortedByOrderDesc`).
 *
 * Cases without `#[Order]` are treated as PHP_INT_MAX (sorted last).
 */
trait HasOrder
{
    public function getOrder(): int
    {
        return AttributesCache::for($this)->order ?? PHP_INT_MAX;
    }

    /**
     * <0 when $this comes before $other, 0 when equal, >0 when after.
     */
    public function compareTo(UnitEnum $other): int
    {
        $self = $this->getOrder();
        $that = method_exists($other, 'getOrder')
            ? (int) $other->getOrder()
            : (AttributesCache::for($other)->order ?? PHP_INT_MAX);

        return $self <=> $that;
    }

    public function isHigherThan(UnitEnum $other): bool
    {
        return $this->compareTo($other) > 0;
    }

    public function isLowerThan(UnitEnum $other): bool
    {
        return $this->compareTo($other) < 0;
    }

    public function isHigherOrEqual(UnitEnum $other): bool
    {
        return $this->compareTo($other) >= 0;
    }

    public function isLowerOrEqual(UnitEnum $other): bool
    {
        return $this->compareTo($other) <= 0;
    }

    /**
     * @return CasesCollection<int, static>
     */
    public static function sortedByOrder(): CasesCollection
    {
        $cases = static::cases();
        usort(
            $cases,
            static fn (UnitEnum $a, UnitEnum $b): int => (AttributesCache::for($a)->order ?? PHP_INT_MAX)
                <=> (AttributesCache::for($b)->order ?? PHP_INT_MAX),
        );

        return new CasesCollection($cases);
    }

    /**
     * @return CasesCollection<int, static>
     */
    public static function sortedByOrderDesc(): CasesCollection
    {
        $cases = static::sortedByOrder()->all();

        return new CasesCollection(array_reverse($cases));
    }
}
