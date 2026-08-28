<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Concerns;

use UnitEnum;
use Simtabi\Laranail\Enumerator\Support\AttributesCache;
use Simtabi\Laranail\Enumerator\Support\CasesCollection;

/**
 * Attribute-driven ordering. Reads `#[Order]` from each case. Provides
 * instance-level comparison (`compareTo`, `isHigherThan`, `isLowerThan`) and
 * static collection helpers (`sortedByOrder`, `sortedByOrderDesc`).
 *
 * Cases without `#[Order]` are treated as PHP_INT_MAX (sorted last).
 */
trait HasOrder
{
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

    public function getOrder(): int
    {
        return AttributesCache::for($this)->order ?? PHP_INT_MAX;
    }

    /**
     * <0 when $this comes before $other, 0 when equal, >0 when after.
     *
     * `$other->getOrder()` is `mixed` to the analyser — `UnitEnum` declares no
     * such method, so `method_exists()` proves it is callable but says nothing
     * about what it returns. This narrows with `is_int()` rather than casting.
     *
     * The cast was not only a static-analysis complaint: `(int) null` is 0, so
     * an override returning null sorted its case *first*, while the documented
     * behaviour for a case with no order is to sort *last* (PHP_INT_MAX). Any
     * non-int now falls through to the attribute, which is where the default
     * lives.
     */
    public function compareTo(UnitEnum $other): int
    {
        $self = $this->getOrder();

        $reported = method_exists($other, 'getOrder') ? $other->getOrder() : null;
        $that = is_int($reported)
            ? $reported
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
}
