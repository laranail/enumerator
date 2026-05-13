<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Concerns;

use BackedEnum;
use Simtabi\Laranail\Enumerator\Exceptions\InvalidEnumeratorNameException;
use Simtabi\Laranail\Enumerator\Exceptions\InvalidEnumeratorValueException;
use Simtabi\Laranail\Enumerator\Support\CasesCollection;
use ValueError;

/**
 * Static hydration helpers — fromName / tryFromName / fromMeta / tryFromMeta.
 *
 * Native enums already expose ::from()/::tryFrom() for backed enums; this
 * trait fills the gap for name-based and meta-based lookups.
 */
trait HasFromHelpers
{
    /**
     * Hydrate a case by its name. Throws on miss.
     */
    public static function fromName(string $name): static
    {
        $case = static::tryFromName($name);
        if ($case === null) {
            throw new InvalidEnumeratorNameException(sprintf(
                'Case "%s" is not a valid name on %s.',
                $name,
                static::class,
            ));
        }

        return $case;
    }

    /**
     * Hydrate a case by its name. Returns null on miss.
     */
    public static function tryFromName(string $name): ?static
    {
        foreach (static::cases() as $case) {
            if ($case->name === $name) {
                /** @var static $case */
                return $case;
            }
        }

        return null;
    }

    /**
     * Hydrate every case whose meta key equals the given value. Throws if
     * no case matches.
     *
     * @return CasesCollection<int, static>
     */
    public static function fromMeta(string $key, mixed $value = true): CasesCollection
    {
        $hits = static::tryFromMeta($key, $value);
        if ($hits === null) {
            throw new ValueError(sprintf(
                'No case on %s has meta "%s" matching the given value.',
                static::class,
                $key,
            ));
        }

        return $hits;
    }

    /**
     * Hydrate every case whose meta key equals the given value. Returns null
     * if no case matches.
     *
     * @return CasesCollection<int, static>|null
     */
    public static function tryFromMeta(string $key, mixed $value = true): ?CasesCollection
    {
        $hits = [];
        foreach (static::cases() as $case) {
            if (! method_exists($case, 'meta')) {
                continue;
            }
            $candidate = $case->meta($key);
            $matches = is_callable($value) ? (bool) $value($candidate) : $candidate === $value;
            if ($matches) {
                $hits[] = $case;
            }
        }

        return $hits === [] ? null : new CasesCollection($hits);
    }

    /**
     * Coerce a value into a case. Accepts the backed value (for backed enums)
     * or the case name (for pure enums or as a fallback).
     */
    public static function coerce(string|int|null $valueOrName): ?static
    {
        if ($valueOrName === null) {
            return null;
        }

        if (is_subclass_of(static::class, BackedEnum::class)) {
            /** @var BackedEnum|null $byValue */
            $byValue = static::tryFrom($valueOrName);
            if ($byValue !== null) {
                /** @var static $byValue */
                return $byValue;
            }
        }

        if (is_string($valueOrName)) {
            return static::tryFromName($valueOrName);
        }

        return null;
    }

    /**
     * Like coerce() but throws.
     */
    public static function fromAny(string|int $valueOrName): static
    {
        $case = static::coerce($valueOrName);
        if ($case === null) {
            throw new InvalidEnumeratorValueException(sprintf(
                'Value "%s" does not match any value or name on %s.',
                (string) $valueOrName,
                static::class,
            ));
        }

        return $case;
    }
}
