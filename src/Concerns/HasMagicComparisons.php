<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Concerns;

use Illuminate\Support\Str;
use Simtabi\Laranail\Enumerator\Exceptions\AmbiguousMagicCallException;

/**
 * Opt-in magic comparison helpers — `$case->isActive()`, `$case->isNotBanned()`,
 * `$case->isOneOf(Status::Active, Status::Pending)`.
 *
 * Case-name resolution is case-insensitive by default
 * (`config('enumerator.magic.case_insensitive_method_names')`); when two cases
 * differ only by capitalisation the call resolves to the exact-match case,
 * falling back to `ambiguous_resolution` config when no exact match exists.
 *
 * Plug into `ResolvesMagicCalls` via `magicCompare()` — do not declare `__call`
 * directly to avoid trait conflicts.
 */
trait HasMagicComparisons
{
    public function isOneOf(self ...$cases): bool
    {
        return in_array($this, $cases, true);
    }

    public function isNoneOf(self ...$cases): bool
    {
        return ! $this->isOneOf(...$cases);
    }

    /**
     * Handler invoked by ResolvesMagicCalls. Return `[$bool]` on a hit, null
     * to let other handlers run.
     *
     * @return array{0: mixed}|null
     */
    protected function magicCompare(string $method, array $arguments): ?array
    {
        if (! Str::startsWith($method, 'is') || $arguments !== []) {
            return null;
        }

        $negate = false;
        $remainder = substr($method, 2);
        if (Str::startsWith($remainder, 'Not')) {
            $negate = true;
            $remainder = substr($remainder, 3);
        }
        if ($remainder === '') {
            return null;
        }

        $target = $this->resolveCaseByName($remainder);
        if ($target === null) {
            return null;
        }

        $matches = $this === $target;

        return [$negate ? ! $matches : $matches];
    }

    private function resolveCaseByName(string $needle): ?self
    {
        $cases = static::cases();

        // Exact-match always wins.
        foreach ($cases as $case) {
            if ($case->name === $needle) {
                return $case;
            }
        }

        $insensitive = function_exists('config')
            ? (bool) (config('enumerator.magic.case_insensitive_method_names') ?? true)
            : true;
        if (! $insensitive) {
            return null;
        }

        $needleLower = Str::lower($needle);
        $hits = [];
        foreach ($cases as $case) {
            if (Str::lower($case->name) === $needleLower) {
                $hits[] = $case;
            }
        }

        if ($hits === []) {
            return null;
        }
        if (count($hits) === 1) {
            return $hits[0];
        }

        $resolution = function_exists('config')
            ? (string) (config('enumerator.magic.ambiguous_resolution') ?? 'throw')
            : 'throw';

        return match ($resolution) {
            'first' => $hits[0],
            'null' => null,
            default => throw new AmbiguousMagicCallException(sprintf(
                'Case lookup "%s" on %s is ambiguous (matches: %s).',
                $needle,
                static::class,
                implode(', ', array_map(static fn (self $c): string => $c->name, $hits)),
            )),
        };
    }
}
