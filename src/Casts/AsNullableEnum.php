<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;
use Simtabi\Laranail\Enumerator\Contracts\Enumerator;
use Simtabi\Laranail\Enumerator\Exceptions\InvalidEnumeratorValueException;

/**
 * Forgiving variant of AsEnum: invalid stored values resolve to null instead
 * of throwing. Useful for legacy data or external imports.
 *
 * @implements CastsAttributes<object, mixed>
 */
final class AsNullableEnum implements CastsAttributes
{
    /**
     * @param  class-string  $enumClass
     */
    public function __construct(public readonly string $enumClass)
    {
        // same constructor-time class validation as AsEnum so
        // misconfiguration fails fast at boot.
        if (! is_subclass_of($enumClass, Enumerator::class) && ! enum_exists($enumClass)) {
            throw new InvalidArgumentException(sprintf(
                'Cast target %s must implement %s.',
                $enumClass,
                Enumerator::class,
            ));
        }
    }

    public static function of(string $enumClass): string
    {
        return self::class . ':' . $enumClass;
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): ?object
    {
        if ($value === null) {
            return null;
        }
        try {
            return (new AsEnum($this->enumClass))->get($model, $key, $value, $attributes);
        } catch (InvalidEnumeratorValueException) {
            return null;
        }
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): null|string|int
    {
        if ($value === null) {
            return null;
        }
        try {
            return (new AsEnum($this->enumClass))->set($model, $key, $value, $attributes);
        } catch (InvalidEnumeratorValueException) {
            return null;
        }
    }
}
