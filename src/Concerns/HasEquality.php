<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Concerns;

use BackedEnum;
use UnitEnum;

/**
 * Equality + membership helpers shared by every Enumerator.
 *
 * `is()` / `isNot()` accept the case, its value, or its name. `in()` /
 * `notIn()` accept arrays of any of those.
 */
trait HasEquality
{
    public function is(mixed $target): bool
    {
        if ($target === $this) {
            return true;
        }
        if (! $this instanceof UnitEnum) {
            return false;
        }
        if ($this instanceof BackedEnum && $target === $this->value) {
            return true;
        }

        return is_string($target) && $target === $this->name;
    }

    public function isNot(mixed $target): bool
    {
        return ! $this->is($target);
    }

    /**
     * @param  iterable<int, mixed>  $targets
     */
    public function in(iterable $targets): bool
    {
        foreach ($targets as $target) {
            if ($this->is($target)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  iterable<int, mixed>  $targets
     */
    public function notIn(iterable $targets): bool
    {
        return ! $this->in($targets);
    }

    public function equals(?object $other): bool
    {
        if ($other === null) {
            return false;
        }

        return $other::class === static::class && $other === $this;
    }
}
