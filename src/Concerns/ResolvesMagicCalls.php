<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Concerns;

use Simtabi\Laranail\Enumerator\Exceptions\AmbiguousMagicCallException;

/**
 * Dispatch chain for `__call` magic.
 *
 * Native enums can declare `__call` only via a trait. We funnel all magic
 * lookups through one method so multiple opt-in concerns (magic comparisons,
 * invokable cases, attribute meta accessors) can co-exist without each
 * needing its own conflict-resolved `__call`.
 *
 * Dispatch order (first hit wins):
 *   1. Magic comparison    (isFoo() / isNotFoo()) — provided by HasMagicComparisons
 *   2. AmbiguousMagicCallException when 'ambiguous_resolution' === 'throw'
 *
 * Concerns that want to participate implement
 *     `protected function magicCall_<x>(string $method, array $args): ?array`
 * and return `[$result]` on a hit, `null` to pass.
 *
 * A meta-key accessor trait may be added in a future release.
 */
trait ResolvesMagicCalls
{
    /**
     * Ordered list of handler method names tried by `__call`. Override
     * in a downstream trait or enum to register additional dispatchers:
     *
     *     protected static function magicCallHandlers(): array
     *     {
     *         return [...parent::magicCallHandlers(), 'magicMyThing'];
     *     }
     *
     * Each handler must accept `(string $method, array $args)` and return
     * `[$result]` on a hit or `null` to pass.
     *
     * @return list<string>
     */
    protected static function magicCallHandlers(): array
    {
        return ['magicCompare'];
    }

    /**
     * @param  array<int, mixed>  $arguments
     */
    public function __call(string $method, array $arguments): mixed
    {
        $hits = [];
        foreach (static::magicCallHandlers() as $handler) {
            if (! method_exists($this, $handler)) {
                continue;
            }
            /** @var array{0: mixed}|null $result */
            $result = $this->{$handler}($method, $arguments);
            if ($result !== null) {
                $hits[] = [$handler, $result[0]];
            }
        }

        if ($hits === []) {
            throw new \BadMethodCallException(sprintf(
                'Undefined method %s::%s()',
                static::class,
                $method,
            ));
        }

        if (count($hits) === 1) {
            return $hits[0][1];
        }

        $resolution = function_exists('config')
            ? (string) (config('enumerator.magic.ambiguous_resolution') ?? 'throw')
            : 'throw';

        return match ($resolution) {
            'first' => $hits[0][1],
            'null' => null,
            default => throw new AmbiguousMagicCallException(sprintf(
                'Method %s::%s() is ambiguous; resolved by handlers: %s.',
                static::class,
                $method,
                implode(', ', array_column($hits, 0)),
            )),
        };
    }
}
