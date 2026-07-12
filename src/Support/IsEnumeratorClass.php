<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Support;

use BackedEnum;
use InvalidArgumentException;
use Simtabi\Laranail\Enumerator\AbstractEnumeratorClass;
use UnitEnum;

/**
 * Unifies the native-enum / class-const enum branching that the package's
 * integrations, rules, route binder, and console commands previously
 * duplicated. Closes .
 *
 * Three public methods only. Keep this class tight — R9
 * explicitly limits scope creep here.
 */
final class IsEnumeratorClass
{
    /**
     * True when $class is something this package can drive — a native
     * PHP 8.3+ enum or an AbstractEnumeratorClass subclass.
     */
    public static function check(string $class): bool
    {
        return enum_exists($class)
            || is_subclass_of($class, AbstractEnumeratorClass::class);
    }

    /**
     * Iterate over a class's cases regardless of which path it's on.
     * Yields nothing (does not throw) when the class isn't enumerator-shaped.
     *
     * @param  class-string  $class
     * @return iterable<int, object>
     */
    public static function casesOf(string $class): iterable
    {
        if (enum_exists($class)) {
            yield from $class::cases();

            return;
        }

        if (is_subclass_of($class, AbstractEnumeratorClass::class)) {
            yield from $class::cases();
        }
    }

    /**
     * Stable identifier for any case instance — backing value for backed
     * enums, name for pure enums, getValue() for class-const cases.
     */
    public static function valueOf(object $case): string|int
    {
        if ($case instanceof BackedEnum) {
            return $case->value;
        }
        if ($case instanceof UnitEnum) {
            return $case->name;
        }
        if (method_exists($case, 'getValue')) {
            /** @var string|int $value */
            $value = $case->getValue();

            return $value;
        }

        throw new InvalidArgumentException(sprintf(
            'Cannot resolve enumerator value for %s.',
            $case::class,
        ));
    }
}
