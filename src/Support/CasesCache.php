<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Support;

use Illuminate\Support\Str;
use ReflectionClass;
use ReflectionClassConstant;
use UnitEnum;

/**
 * Reflection cache for enum cases — native and class-const enums alike.
 * Returns the canonical list of cases for a given class without paying
 * reflection cost more than once per process.
 */
final class CasesCache
{
    /** @var array<class-string, list<UnitEnum>|array<string, mixed>> */
    private static array $memo = [];

    /**
     * Return native enum cases. Equivalent to `$class::cases()`, memoised.
     *
     * @template TCase of UnitEnum
     *
     * @param  class-string<TCase>  $class
     * @return list<TCase>
     */
    public static function nativeCases(string $class): array
    {
        if (! isset(self::$memo[$class])) {
            self::$memo[$class] = $class::cases();
        }

        /** @var list<TCase> */
        return self::$memo[$class];
    }

    /**
     * Return a map of constant-name => value for a class-const enum.
     *
     * @param  class-string  $class
     * @return array<string, string|int>
     */
    public static function classConstants(string $class): array
    {
        $key = $class . '::__constants__';
        if (isset(self::$memo[$key])) {
            /** @var array<string, string|int> */
            return self::$memo[$key];
        }

        $reflection = new ReflectionClass($class);
        $map = [];
        foreach ($reflection->getReflectionConstants() as $constant) {
            if (! $constant->isPublic()) {
                continue;
            }
            if (Str::startsWith($constant->getName(), '__')) {
                continue;
            }
            $value = $constant->getValue();
            if (is_string($value) || is_int($value)) {
                $map[$constant->getName()] = $value;
            }
        }

        /** @var array<string, string|int> $map */
        self::$memo[$key] = $map;

        return $map;
    }

    /**
     * @param  class-string  $class
     */
    public static function reflectConstant(string $class, string $name): ReflectionClassConstant
    {
        return new ReflectionClassConstant($class, $name);
    }

    public static function flush(): void
    {
        self::$memo = [];
    }

    /**
     * Inject a class-constants map directly into the memo, bypassing
     * reflection. Used by `DynamicEnums\DatabaseBackedEnum` so
     * runtime-loaded cases (from a database table) flow through the same
     * `classConstants()` lookup path as static `public const` constants.
     *
     * @param  class-string  $class
     * @param  array<string, string|int>  $constants  name → value map
     */
    public static function setConstants(string $class, array $constants): void
    {
        self::$memo[$class . '::__constants__'] = $constants;
    }

    /**
     * Export the in-memory memo for `LayeredCache` persistence (/
     * ). Native-enum cases are case singletons that `var_export`
     * handles natively; class-const constant maps are plain arrays.
     *
     * @return array<string, mixed>
     */
    public static function snapshot(): array
    {
        return self::$memo;
    }

    /**
     * Restore the in-memory memo from a `snapshot()` payload.
     *
     * @param  array<string, mixed>  $snapshot
     */
    public static function restore(array $snapshot): void
    {
        foreach ($snapshot as $key => $value) {
            self::$memo[$key] = $value;
        }
    }
}
