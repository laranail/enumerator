<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;
use Simtabi\Laranail\Enumerator\Contracts\Bitwise;
use Simtabi\Laranail\Enumerator\Helpers\Bitmask;

/**
 * Cast an integer column to/from a Bitmask of a Bitwise enum.
 *
 *     'flags' => AsBitmask::of(FeatureFlagEnum::class),
 *
 * Stored representation: int. Hydrated representation: Bitmask value object.
 *
 * @implements CastsAttributes<Bitmask, int|Bitmask>
 */
final class AsBitmask implements CastsAttributes
{
    /**
     * @param  class-string<Bitwise>  $enumClass
     */
    public function __construct(public readonly string $enumClass)
    {
        if (! is_subclass_of($enumClass, Bitwise::class)) {
            throw new InvalidArgumentException(sprintf(
                'Cast target %s must implement %s.',
                $enumClass,
                Bitwise::class,
            ));
        }
    }

    /**
     * @param  class-string<Bitwise>  $enumClass
     */
    public static function of(string $enumClass): string
    {
        return self::class . ':' . $enumClass;
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): ?Bitmask
    {
        if ($value === null) {
            return null;
        }
        if ($value instanceof Bitmask) {
            return $value;
        }
        if (! is_numeric($value)) {
            return null;
        }

        $class = $this->enumClass;

        /** @var Bitmask|null */
        return $class::tryMask((int) $value);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): ?int
    {
        if ($value === null) {
            return null;
        }
        if ($value instanceof Bitmask) {
            return $value->toInt();
        }
        // accept a single Bitwise case → wrap into a 1-case mask.
        // Construct Bitmask directly (mask() comes from the HasBitmask trait,
        // which PHPStan can't see through the Bitwise marker interface).
        if ($value instanceof Bitwise) {
            return (new Bitmask($this->enumClass, [$value]))->toInt();
        }
        if (is_int($value)) {
            return $value;
        }
        if (is_numeric($value)) {
            return (int) $value;
        }

        return null;
    }
}
