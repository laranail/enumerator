<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Casts;

use BackedEnum;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;
use Simtabi\Laranail\Enumerator\AbstractEnumeratorClass;
use Simtabi\Laranail\Enumerator\Contracts\Enumerator;
use Simtabi\Laranail\Enumerator\Exceptions\InvalidEnumeratorValueException;
use UnitEnum;

/**
 * Eloquent attribute cast for any Enumerator (native enum OR
 * AbstractEnumeratorClass subclass). Reads/writes the backing value.
 *
 * Usage in a model's casts():
 *
 *     'status' => AsEnum::of(UserStatusEnum::class),
 *     // or
 *     'status' => AsEnum::class.':'.UserStatusEnum::class,
 *
 * Throws on invalid stored values unless wrapped with AsNullableEnum.
 *
 * @implements CastsAttributes<object, mixed>
 */
final class AsEnum implements CastsAttributes
{
    /**
     * @param  class-string<Enumerator>  $enumClass
     */
    public function __construct(public readonly string $enumClass)
    {
        if (! is_subclass_of($enumClass, Enumerator::class) && ! enum_exists($enumClass)) {
            throw new InvalidArgumentException(sprintf(
                'Cast target %s must implement %s.',
                $enumClass,
                Enumerator::class,
            ));
        }
    }

    /**
     * Helper for `casts()`: AsEnum::of(MyEnum::class).
     *
     * @param  class-string<Enumerator>  $enumClass
     */
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
        if ($value instanceof $this->enumClass) {
            return $value;
        }

        return $this->hydrate($value);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): null|string|int
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof BackedEnum) {
            return $value->value;
        }
        if ($value instanceof AbstractEnumeratorClass) {
            /** @var string|int|null */
            return $value->getValue();
        }
        if ($value instanceof UnitEnum) {
            return $value->name;
        }
        if (is_string($value) || is_int($value)) {
            // Validate by hydrating
            $this->hydrate($value);

            return $value;
        }

        throw new InvalidEnumeratorValueException(sprintf(
            'Cannot persist value of type %s for %s.',
            get_debug_type($value),
            $this->enumClass,
        ));
    }

    private function hydrate(mixed $value): object
    {
        if (! is_string($value) && ! is_int($value)) {
            throw new InvalidEnumeratorValueException(sprintf(
                'Stored value of type %s is not coercible to %s.',
                get_debug_type($value),
                $this->enumClass,
            ));
        }

        $class = $this->enumClass;

        if (enum_exists($class)) {
            /** @var class-string<BackedEnum>|class-string<UnitEnum> $class */
            if (is_subclass_of($class, BackedEnum::class)) {
                $case = $class::tryFrom($value);
                if ($case === null && method_exists($class, 'tryFromName') && is_string($value)) {
                    $case = $class::tryFromName($value);
                }
                if ($case === null) {
                    throw new InvalidEnumeratorValueException(sprintf(
                        'Stored value "%s" is not a valid case of %s.',
                        (string) $value,
                        $class,
                    ));
                }

                return $case;
            }

            // Pure enum — only name-based lookup possible
            if (method_exists($class, 'tryFromName') && is_string($value)) {
                $case = $class::tryFromName($value);
                if ($case !== null) {
                    return $case;
                }
            }

            throw new InvalidEnumeratorValueException(sprintf(
                'Stored value "%s" cannot hydrate the pure enum %s.',
                (string) $value,
                $class,
            ));
        }

        // Class-const enum
        /** @var class-string<AbstractEnumeratorClass> $class */
        return $class::fromValue($value);
    }
}
