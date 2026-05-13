<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Support;

use BackedEnum;
use Illuminate\Support\Collection;
use Simtabi\Laranail\Enumerator\Helpers\Humanizer;
use UnitEnum;

/**
 * Specialised Collection of enum cases. Adds enum-aware helpers on top of
 * Laravel's Collection. Returns plain arrays from value/name/label projection
 * methods to mirror Cerbero's CasesCollection ergonomics.
 *
 * @template TCase of UnitEnum
 *
 * @extends Collection<int, TCase>
 */
class CasesCollection extends Collection
{
    /**
     * Return an array of case names.
     *
     * @return array<int, string>
     */
    public function names(): array
    {
        $out = [];
        foreach ($this->items as $case) {
            if ($case instanceof UnitEnum) {
                $out[] = $case->name;
            }
        }

        return $out;
    }

    /**
     * Return an array of backing values (for backed enums) or names (pure).
     *
     * Renamed from `values()`: the previous override broke the
     * `Illuminate\Support\Collection::values(): Collection` contract.
     * `values()` now keeps the parent semantics (re-indexed Collection of
     * cases); use this method to project to a flat scalar array.
     *
     * @return array<int, string|int>
     */
    public function flatValues(): array
    {
        $out = [];
        foreach ($this->items as $case) {
            if ($case instanceof UnitEnum) {
                $out[] = $case instanceof BackedEnum ? $case->value : $case->name;
            }
        }

        return $out;
    }

    /**
     * Return [value => label] pairs (label resolved via the case's label()
     * method if present, else humanised name).
     *
     * @return array<int|string, string>
     */
    public function labels(): array
    {
        $out = [];
        foreach ($this->items as $case) {
            $key = $case instanceof BackedEnum ? $case->value : $case->name;
            $out[$key] = $this->resolveLabel($case);
        }

        return $out;
    }

    /**
     * Filter cases that match the given name (string).
     *
     * @return static<int, TCase>
     */
    public function whereName(string $name): static
    {
        $filtered = $this->filter(static fn (UnitEnum $case): bool => $case->name === $name);

        /** @var static<int, TCase> */
        return new static(array_values($filtered->all()));
    }

    /**
     * Filter cases that match the given backing value.
     *
     * @return static<int, TCase>
     */
    public function whereValue(string|int $value): static
    {
        $filtered = $this->filter(
            static fn (UnitEnum $case): bool => $case instanceof BackedEnum && $case->value === $value,
        );

        /** @var static<int, TCase> */
        return new static(array_values($filtered->all()));
    }

    /**
     * Filter cases whose meta key equals the given value.
     *
     * @return static<int, TCase>
     */
    public function whereMeta(string $key, mixed $value = true): static
    {
        $filtered = $this->filter(static function (UnitEnum $case) use ($key, $value): bool {
            if (! method_exists($case, 'meta')) {
                return false;
            }

            return $case->meta($key) === $value;
        });

        /** @var static<int, TCase> */
        return new static(array_values($filtered->all()));
    }

    /**
     * Exclude cases by name. (Renamed to avoid collision with
     * `Illuminate\Support\Collection::except($keys)`.)
     *
     * @return static<int, TCase>
     */
    public function exceptByName(string ...$names): static
    {
        $filtered = $this->filter(
            static fn (UnitEnum $case): bool => ! in_array($case->name, $names, true),
        );

        /** @var static<int, TCase> */
        return new static(array_values($filtered->all()));
    }

    /**
     * Keep only cases with the given names. (Renamed to avoid collision with
     * `Illuminate\Support\Collection::only($keys)`.)
     *
     * @return static<int, TCase>
     */
    public function onlyByName(string ...$names): static
    {
        $filtered = $this->filter(
            static fn (UnitEnum $case): bool => in_array($case->name, $names, true),
        );

        /** @var static<int, TCase> */
        return new static(array_values($filtered->all()));
    }

    /**
     * Key by name.
     *
     * @return static<string, TCase>
     */
    public function keyByName(): static
    {
        return $this->keyBy(static fn (UnitEnum $case): string => $case->name);
    }

    /**
     * Key by value.
     *
     * @return static<int|string, TCase>
     */
    public function keyByValue(): static
    {
        return $this->keyBy(
            static fn (UnitEnum $case): string|int => $case instanceof BackedEnum ? $case->value : $case->name,
        );
    }

    /**
     * Pluck a case method/property; falls back to the value when the projector
     * is a callable. The key callable receives the case as its argument.
     *
     * Renamed from `pluck()`: the previous override broke the
     * `Illuminate\Support\Collection::pluck(): Collection` contract.
     * `pluck()` now keeps the parent semantics; use this method to project
     * to a flat array.
     *
     * @return array<int|string, mixed>
     */
    public function flatPluck(mixed $value, mixed $key = null): array
    {
        $out = [];
        foreach ($this->items as $case) {
            $resolved = $this->resolveProjector($case, $value);
            if ($key !== null) {
                $out[$this->resolveProjector($case, $key)] = $resolved;
            } else {
                $out[] = $resolved;
            }
        }

        return $out;
    }

    /**
     * Render this collection as [['value' => ..., 'name' => ..., 'label' => ...]].
     *
     * @return array<int, array{value: string|int, name: string, label: string}>
     */
    public function toRichArray(): array
    {
        $out = [];
        foreach ($this->items as $case) {
            $out[] = [
                'value' => $case instanceof BackedEnum ? $case->value : $case->name,
                'name' => $case->name,
                'label' => $this->resolveLabel($case),
            ];
        }

        return $out;
    }

    private function resolveLabel(UnitEnum $case): string
    {
        if (method_exists($case, 'label')) {
            return (string) $case->label();
        }

        return Humanizer::humanize($case->name);
    }

    private function resolveProjector(UnitEnum $case, mixed $projector): mixed
    {
        if (is_callable($projector)) {
            return $projector($case);
        }

        if (is_string($projector) && method_exists($case, $projector)) {
            return $case->{$projector}();
        }

        if (is_string($projector) && property_exists($case, $projector)) {
            return $case->{$projector};
        }

        return $case;
    }
}
