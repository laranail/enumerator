<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Helpers;

use ArrayIterator;
use BackedEnum;
use Countable;
use IteratorAggregate;
use JsonSerializable;
use Simtabi\Laranail\Enumerator\Contracts\Bitwise;
use Simtabi\Laranail\Enumerator\Contracts\Enumerator;
use Simtabi\Laranail\Enumerator\Exceptions\InvalidBitmaskException;
use Simtabi\Laranail\Enumerator\Support\AttributesCache;
use Traversable;
use UnitEnum;

/**
 * Bitmask value object. Combines one or more Bitwise enum cases into a single
 * integer mask, backed by the `#[Bit]` attribute on each case.
 *
 * Independent of the enum's backing type — the case can be int-backed,
 * string-backed, or pure; only the #[Bit] attribute value matters here.
 *
 * @template TCase of UnitEnum&Enumerator&Bitwise
 *
 * @implements IteratorAggregate<int, TCase>
 */
final class Bitmask implements Countable, IteratorAggregate, JsonSerializable
{
    /**
     * @param  class-string<TCase>  $enumClass
     * @param  array<int, TCase>  $cases
     */
    public function __construct(
        public readonly string $enumClass,
        private array $cases,
    ) {
        if (! is_subclass_of($enumClass, Bitwise::class)) {
            throw new InvalidBitmaskException(sprintf(
                'Enum %s must implement %s to be used as a bitmask.',
                $enumClass,
                Bitwise::class,
            ));
        }

        // validate every case belongs to $enumClass. Catches
        // consumer typos at construct time rather than at toInt().
        foreach ($cases as $case) {
            if (! $case instanceof $enumClass) {
                throw new InvalidBitmaskException(sprintf(
                    'Case %s is not an instance of %s.',
                    $case instanceof UnitEnum ? $case::class . '::' . $case->name : get_debug_type($case),
                    $enumClass,
                ));
            }
        }

        $this->cases = array_values(array_unique($cases, SORT_REGULAR));
    }

    /**
     * Combined integer mask.
     */
    public function toInt(): int
    {
        $mask = 0;
        foreach ($this->cases as $case) {
            $mask |= AttributesCache::bitFor($case);
        }

        return $mask;
    }

    /**
     * Whether the given case is part of this mask.
     *
     * @param  TCase  $case
     */
    public function has(UnitEnum $case): bool
    {
        return in_array($case, $this->cases, true);
    }

    /**
     * Whether none of the given cases are part of the mask.
     *
     * @param  TCase  ...$cases
     */
    public function hasNone(UnitEnum ...$cases): bool
    {
        foreach ($cases as $case) {
            if ($this->has($case)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Whether every given case is part of the mask.
     *
     * @param  TCase  ...$cases
     */
    public function hasAll(UnitEnum ...$cases): bool
    {
        foreach ($cases as $case) {
            if (! $this->has($case)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Whether at least one given case is part of the mask.
     *
     * @param  TCase  ...$cases
     */
    public function hasAny(UnitEnum ...$cases): bool
    {
        foreach ($cases as $case) {
            if ($this->has($case)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Return a new mask with the given cases added (immutable).
     *
     * @param  TCase  ...$cases
     */
    public function add(UnitEnum ...$cases): self
    {
        $merged = array_merge($this->cases, $cases);

        return new self($this->enumClass, $merged);
    }

    /**
     * Return a new mask with the given cases removed (immutable).
     *
     * @param  TCase  ...$cases
     */
    public function remove(UnitEnum ...$cases): self
    {
        $filtered = array_values(array_filter(
            $this->cases,
            static fn (UnitEnum $existing): bool => ! in_array($existing, $cases, true),
        ));

        return new self($this->enumClass, $filtered);
    }

    /**
     * All cases as an array.
     *
     * @return array<int, TCase>
     */
    public function all(): array
    {
        return $this->cases;
    }

    /**
     * Backing values of each case (for string-backed enums returns strings,
     * int-backed returns ints, pure returns names).
     *
     * @return array<int, string|int>
     */
    public function values(): array
    {
        return array_map(
            static fn (UnitEnum $case): string|int => $case instanceof BackedEnum ? $case->value : $case->name,
            $this->cases,
        );
    }

    /**
     * Case names.
     *
     * @return array<int, string>
     */
    public function names(): array
    {
        return array_map(static fn (UnitEnum $case): string => $case->name, $this->cases);
    }

    /**
     * Labels for every case (calls ->label() on each — assumes
     * HasEnumeratorBehavior).
     *
     * @return array<int, string>
     */
    public function labels(): array
    {
        $labels = [];
        foreach ($this->cases as $case) {
            $labels[] = method_exists($case, 'label')
                ? (string) $case->label()
                : Humanizer::humanize($case->name);
        }

        return $labels;
    }

    public function count(): int
    {
        return count($this->cases);
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->cases);
    }

    /**
     * @return array{enum: class-string<TCase>, mask: int, cases: array<int, string>}
     */
    public function jsonSerialize(): array
    {
        return [
            'enum' => $this->enumClass,
            'mask' => $this->toInt(),
            'cases' => $this->names(),
        ];
    }

    public function __toString(): string
    {
        return (string) $this->toInt();
    }
}
