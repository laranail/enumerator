<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Support;

use ReflectionAttribute;
use ReflectionClass;
use ReflectionClassConstant;
use ReflectionEnum;
use Simtabi\Laranail\Enumerator\Attributes\Bit;
use Simtabi\Laranail\Enumerator\Attributes\Color;
use Simtabi\Laranail\Enumerator\Attributes\CssClass;
use Simtabi\Laranail\Enumerator\Attributes\Description;
use Simtabi\Laranail\Enumerator\Attributes\Help;
use Simtabi\Laranail\Enumerator\Attributes\Icon;
use Simtabi\Laranail\Enumerator\Attributes\Label;
use Simtabi\Laranail\Enumerator\Attributes\Meta;
use Simtabi\Laranail\Enumerator\Attributes\Order;
use Simtabi\Laranail\Enumerator\Exceptions\InvalidBitmaskException;
use UnitEnum;

/**
 * Per-case attribute reflection cache. Reads every supported attribute off a
 * case/constant once and exposes a compact bag of resolved values.
 *
 * Reads the LayeredCache when registered (see EnumeratorRegistry::cache());
 * otherwise falls back to in-memory only.
 */
final class AttributesCache
{
    /** @var array<string, AttributeBag> */
    private static array $memo = [];

    /**
     * Resolve the attribute bag for a native enum case OR a class-const value.
     *
     * @param  UnitEnum|object  $caseOrInstance  enum case or AbstractEnumeratorClass instance
     */
    public static function for(object $caseOrInstance): AttributeBag
    {
        if ($caseOrInstance instanceof UnitEnum) {
            $key = $caseOrInstance::class . '::' . $caseOrInstance->name;
            if (isset(self::$memo[$key])) {
                return self::$memo[$key];
            }

            return self::$memo[$key] = self::readNativeEnumCase($caseOrInstance);
        }

        // Class-const enum instance — read constant attributes
        $class = $caseOrInstance::class;
        $constantName = method_exists($caseOrInstance, 'getKey')
            ? (string) $caseOrInstance->getKey()
            : null;

        if ($constantName === null) {
            return new AttributeBag;
        }

        $key = $class . '::' . $constantName;
        if (isset(self::$memo[$key])) {
            return self::$memo[$key];
        }

        return self::$memo[$key] = self::readClassConstant($class, $constantName);
    }

    /**
     * Resolve the class-level attribute bag (Description on the enum class).
     *
     * @param  class-string  $class
     */
    public static function forClass(string $class): AttributeBag
    {
        $key = $class . '::__class__';
        if (isset(self::$memo[$key])) {
            return self::$memo[$key];
        }

        $reflection = new ReflectionClass($class);

        $bag = new AttributeBag;
        foreach ($reflection->getAttributes() as $attr) {
            self::apply($bag, $attr);
        }

        return self::$memo[$key] = $bag;
    }

    /**
     * Resolve the integer bit assigned to a Bitwise case. Throws if the
     * case has no `#[Bit]` attribute, or if any sibling case violates the
     * powers-of-two/distinct-bits invariants.
     */
    public static function bitFor(UnitEnum $case): int
    {
        $bag = self::for($case);
        if ($bag->bit === null) {
            throw new InvalidBitmaskException(sprintf(
                'Case %s::%s is used in a bitmask but has no #[Bit] attribute.',
                $case::class,
                $case->name,
            ));
        }
        self::validateBits($case::class);

        return $bag->bit;
    }

    /**
     * Run validation rules over every #[Bit] attribute on the given enum.
     * Memoised to once-per-class.
     *
     * @param  class-string  $class
     */
    public static function validateBits(string $class): void
    {
        $key = $class . '::__bits_validated__';
        if (isset(self::$memo[$key])) {
            return;
        }

        $seen = [];
        if (enum_exists($class)) {
            foreach ($class::cases() as $case) {
                $bit = self::for($case)->bit;
                if ($bit === null) {
                    continue;
                }
                self::assertPowerOfTwo($class, $case->name, $bit);
                if (in_array($bit, $seen, true)) {
                    throw new InvalidBitmaskException(sprintf(
                        'Duplicate bit %d in enum %s.',
                        $bit,
                        $class,
                    ));
                }
                $seen[] = $bit;
            }
        } else {
            foreach ((new ReflectionClass($class))->getReflectionConstants() as $const) {
                foreach ($const->getAttributes(Bit::class) as $attr) {
                    /** @var Bit $bit */
                    $bit = $attr->newInstance();
                    self::assertPowerOfTwo($class, $const->getName(), $bit->bit);
                    if (in_array($bit->bit, $seen, true)) {
                        throw new InvalidBitmaskException(sprintf(
                            'Duplicate bit %d in enum %s.',
                            $bit->bit,
                            $class,
                        ));
                    }
                    $seen[] = $bit->bit;
                }
            }
        }

        self::$memo[$key] = new AttributeBag;
    }

    private static function assertPowerOfTwo(string $class, string $caseName, int $bit): void
    {
        if ($bit < 1 || ($bit & ($bit - 1)) !== 0) {
            throw new InvalidBitmaskException(sprintf(
                'Bit %d on %s::%s is not a positive power of two.',
                $bit,
                $class,
                $caseName,
            ));
        }
    }

    public static function flush(): void
    {
        self::$memo = [];
    }

    /**
     * Export the in-memory memo as plain arrays for `LayeredCache` persistence
     * (/ ). `AttributeBag` instances become array snapshots
     * that round-trip cleanly via `var_export`.
     *
     * @return array<string, array<string, mixed>>
     */
    public static function snapshot(): array
    {
        $out = [];
        foreach (self::$memo as $key => $bag) {
            if (! $bag instanceof AttributeBag) {
                continue;
            }
            $out[$key] = [
                'label' => $bag->label,
                'description' => $bag->description,
                'color' => $bag->color,
                'icon' => $bag->icon,
                'help' => $bag->help,
                'order' => $bag->order,
                'bit' => $bag->bit,
                'meta' => $bag->meta,
                'cssClasses' => $bag->cssClasses,
            ];
        }

        return $out;
    }

    /**
     * Restore the in-memory memo from a `snapshot()` payload.
     *
     * @param  array<string, array<string, mixed>>  $snapshot
     */
    public static function restore(array $snapshot): void
    {
        foreach ($snapshot as $key => $payload) {
            if (! is_array($payload)) {
                continue;
            }
            $bag = new AttributeBag;
            $bag->label = is_string($payload['label'] ?? null) ? $payload['label'] : null;
            $bag->description = is_string($payload['description'] ?? null) ? $payload['description'] : null;
            $bag->color = is_string($payload['color'] ?? null) ? $payload['color'] : null;
            $bag->icon = is_string($payload['icon'] ?? null) ? $payload['icon'] : null;
            $bag->help = is_string($payload['help'] ?? null) ? $payload['help'] : null;
            $bag->order = is_int($payload['order'] ?? null) ? $payload['order'] : null;
            $bag->bit = is_int($payload['bit'] ?? null) ? $payload['bit'] : null;
            $bag->meta = is_array($payload['meta'] ?? null) ? $payload['meta'] : null;
            /** @var array<string, string> $css */
            $css = is_array($payload['cssClasses'] ?? null) ? $payload['cssClasses'] : [];
            $bag->cssClasses = $css;
            self::$memo[$key] = $bag;
        }
    }

    private static function readNativeEnumCase(UnitEnum $case): AttributeBag
    {
        $reflection = new ReflectionEnum($case::class);
        $constant = $reflection->getCase($case->name);
        $bag = new AttributeBag;
        foreach ($constant->getAttributes() as $attr) {
            self::apply($bag, $attr);
        }

        return $bag;
    }

    /**
     * @param  class-string  $class
     */
    private static function readClassConstant(string $class, string $constant): AttributeBag
    {
        $bag = new AttributeBag;
        try {
            $ref = new ReflectionClassConstant($class, $constant);
        } catch (\ReflectionException) {
            // No physical PHP constant exists for this case. Most likely
            // a `DynamicEnums\DatabaseBackedEnum` case loaded at runtime
            // via `CasesCache::setConstants()`. Return an empty bag so
            // `label()` falls through to the humanized name.
            return $bag;
        }
        foreach ($ref->getAttributes() as $attr) {
            self::apply($bag, $attr);
        }

        return $bag;
    }

    private static function apply(AttributeBag $bag, ReflectionAttribute $attribute): void
    {
        /** @var object $instance */
        $instance = $attribute->newInstance();

        match (true) {
            $instance instanceof Label => $bag->label = $instance->label,
            $instance instanceof Description => $bag->description = $instance->description,
            $instance instanceof Color => $bag->color = $instance->color,
            $instance instanceof Icon => $bag->icon = $instance->icon,
            $instance instanceof Help => $bag->help = $instance->help,
            $instance instanceof Order => $bag->order = $instance->order,
            $instance instanceof Bit => $bag->bit = $instance->bit,
            $instance instanceof Meta => $bag->meta = array_merge($bag->meta ?? [], $instance->values),
            $instance instanceof CssClass => $bag->cssClasses[$instance->framework]
                = trim(($bag->cssClasses[$instance->framework] ?? '') . ' ' . $instance->classes),
            default => null,
        };
    }
}
