<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Concerns;

use Simtabi\Laranail\Enumerator\Helpers\Bitmask;
use Simtabi\Laranail\Enumerator\Support\AttributesCache;
use UnitEnum;

/**
 * Bitmask support for native enums declaring `#[Bit]` attributes on each case.
 * Works for any backing type — int, string, or pure (no backing value).
 *
 * The mask is an int derived from the attribute values, not the enum's
 * backing value. Bits must be distinct powers of two (validated lazily on
 * first call).
 */
trait HasBitmask
{
    /**
     * Build a mask from a set of cases.
     *
     * @return Bitmask<static>
     */
    public static function mask(self|UnitEnum ...$cases): Bitmask
    {
        return new Bitmask(static::class, $cases);
    }

    /**
     * Hydrate a mask from an int. Bits not declared by any case are ignored.
     *
     * @return Bitmask<static>
     */
    public static function fromMask(int $mask): Bitmask
    {
        $cases = [];
        foreach (static::cases() as $case) {
            $bit = AttributesCache::for($case)->bit;
            if ($bit !== null && ($mask & $bit) === $bit) {
                $cases[] = $case;
            }
        }

        return new Bitmask(static::class, $cases);
    }

    /**
     * Safe variant of fromMask — returns null when the mask is null.
     *
     * @return Bitmask<static>|null
     */
    public static function tryMask(?int $mask): ?Bitmask
    {
        if ($mask === null) {
            return null;
        }

        return static::fromMask($mask);
    }

    /**
     * The bit assigned to this case via its `#[Bit]` attribute. Throws when
     * the case has no `#[Bit]` attribute.
     */
    public function bit(): int
    {
        return AttributesCache::bitFor($this);
    }

    /**
     * Map of bit => case name for the enum.
     *
     * @return array<int, string>
     */
    public static function bits(): array
    {
        $out = [];
        foreach (static::cases() as $case) {
            $bit = AttributesCache::for($case)->bit;
            if ($bit !== null) {
                $out[$bit] = $case->name;
            }
        }

        return $out;
    }
}
