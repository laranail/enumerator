<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Concerns;

use BackedEnum;
use Simtabi\Laranail\Enumerator\Support\CasesCollection;
use UnitEnum;

/**
 * Umbrella trait for native PHP 8.3+ enums implementing the
 * `Simtabi\Laranail\Enumerator\Contracts\Enumerator` marker interface.
 *
 * Pulls in every always-on concern:
 *   - HasAttributes     attribute lookup with config-override layer
 *   - HasEquality       is / isNot / in / notIn / equals
 *   - HasFromHelpers    fromName / tryFromName / fromMeta / tryFromMeta / coerce
 *   - IsJsonable        toArray / toJson / jsonSerialize
 *   - IsTranslatable    label / description / help / placeholder
 *   - RendersHtml       toHtml (also delivered separately via HtmlRenderable contract)
 *   - ResolvesMagicCalls central __call dispatcher for opt-in magic concerns
 *
 * Plus collection helpers (`all`, `values`, `names`, `labels`, `options`,
 * `collect`, `count`, `first`, `last`, `random`).
 */
trait HasEnumeratorBehavior
{
    use HasAttributes;
    use HasEquality;
    use HasFromHelpers;
    use IsJsonable;
    use IsTranslatable;
    use RendersHtml;
    use ResolvesMagicCalls;

    /**
     * Alias for `cases()` — kept for parity with class-const APIs.
     *
     * @return array<int, static>
     */
    public static function all(): array
    {
        /** @var array<int, static> */
        return static::cases();
    }

    /**
     * Backing values (backed enums) or names (pure enums).
     *
     * @return array<int, string|int>
     */
    public static function values(): array
    {
        $out = [];
        foreach (static::cases() as $case) {
            $out[] = $case instanceof BackedEnum ? $case->value : $case->name;
        }

        return $out;
    }

    /**
     * @return array<int, string>
     */
    public static function names(): array
    {
        return array_map(static fn (UnitEnum $case): string => $case->name, static::cases());
    }

    /**
     * Map of [value => label].
     *
     * @return array<int|string, string>
     */
    public static function labels(): array
    {
        $out = [];
        foreach (static::cases() as $case) {
            $key = $case instanceof BackedEnum ? $case->value : $case->name;
            $out[$key] = (string) $case->label();
        }

        return $out;
    }

    /**
     * Select-ready options, optionally prefixed with a placeholder.
     *
     * @return array<int|string, string>
     */
    public static function options(?string $placeholder = null): array
    {
        $options = [];
        if ($placeholder !== null) {
            $options[''] = $placeholder;
        }

        return $options + static::labels();
    }

    public static function count(): int
    {
        return count(static::cases());
    }

    public static function first(): static
    {
        $cases = static::cases();

        /** @var static */
        return $cases[0];
    }

    public static function last(): static
    {
        $cases = static::cases();

        /** @var static */
        return $cases[array_key_last($cases)];
    }

    public static function random(): static
    {
        $cases = static::cases();

        /** @var static */
        return $cases[array_rand($cases)];
    }

    public static function has(mixed $target): bool
    {
        foreach (static::cases() as $case) {
            if ($case->is($target)) {
                return true;
            }
        }

        return false;
    }

    public static function doesntHave(mixed $target): bool
    {
        return ! static::has($target);
    }

    public static function isValid(mixed $value): bool
    {
        return static::has($value);
    }

    public static function isPure(): bool
    {
        return ! is_subclass_of(static::class, BackedEnum::class);
    }

    public static function isBacked(): bool
    {
        return is_subclass_of(static::class, BackedEnum::class);
    }

    /**
     * @return CasesCollection<int, static>
     */
    public static function collect(): CasesCollection
    {
        return new CasesCollection(static::cases());
    }
}
