<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Concerns;

use BackedEnum;
use Closure;
use Illuminate\Support\Facades\Cache;
use Simtabi\Laranail\Enumerator\Contracts\Cacheable;
use UnitEnum;

/**
 * Cache-key encapsulation for enum cases.
 *
 * Defines an enum case AS a cache-key namespace, with shorthand
 * methods that proxy to Laravel's `Cache` facade. The case's `key()`
 * is the cache key string (override per-enum if you want a prefix).
 *
 * Usage:
 *
 *     enum CacheKey: string implements \Simtabi\Laranail\Enumerator\Contracts\Cacheable
 *     {
 *         use \Simtabi\Laranail\Enumerator\Concerns\IsCacheKey;
 *
 *         case CurrentUser   = 'user:current';
 *         case TenantConfig  = 'tenant:config';
 *         case BillingPlan   = 'billing:plan';
 *     }
 *
 *     CacheKey::CurrentUser->put($user, ttl: 3600);
 *     CacheKey::CurrentUser->get();
 *     CacheKey::CurrentUser->cached();
 *     CacheKey::CurrentUser->forget();
 *
 *     CacheKey::TenantConfig->remember(
 *         fn () => TenantConfig::fromDatabase(),
 *         ttl: 60 * 5,
 *     );
 *
 * Implements `Concerns\Core\BehaviorCore` would compose this, but
 * IsCacheKey stays standalone so cache-key enums don't accidentally
 * pull in the full HasEnumerator umbrella when only this surface is
 * wanted.
 *
 * Override `key()` (declared on `Contracts\Cacheable`) to customise
 * the cache-key string — by default it's the backing value for
 * backed enums or the case name for pure enums. The default already
 * yields predictable keys when the case has a `:`-separated value.
 *
 * TTL semantics match Laravel's Cache facade:
 *   - `ttl = null`  → store forever
 *   - `ttl = int`   → seconds
 *   - other Laravel-supported TTL types (DateTimeInterface,
 *     DateInterval) flow through cleanly.
 *
 * @phpstan-require-implements Cacheable
 */
trait IsCacheKey
{
    /**
     * Default cache-key string: the case's backing value (backed
     * enums) or name (pure enums). Override in the consumer enum to
     * customise (e.g., add a prefix).
     */
    public function key(): string
    {
        /** @phpstan-var UnitEnum $self */
        $self = $this;

        return $self instanceof BackedEnum ? (string) $self->value : $self->name;
    }

    /**
     * Store `$value` under this case's key. Null TTL stores forever.
     */
    public function put(mixed $value, mixed $ttl = null): bool
    {
        if ($ttl === null) {
            return Cache::forever($this->key(), $value);
        }

        return Cache::put($this->key(), $value, $ttl);
    }

    /**
     * Read the value under this case's key. Returns `$default` when
     * the key is absent.
     */
    public function get(mixed $default = null): mixed
    {
        return Cache::get($this->key(), $default);
    }

    /**
     * Drop the cached value (if present). Returns true on success.
     */
    public function forget(): bool
    {
        return Cache::forget($this->key());
    }

    /**
     * Check whether a value is currently cached under this case's key.
     *
     * Named `cached()` rather than `has()` so it never collides with
     * `Enumerator::has(target)` (a static membership check shipped via
     * `HasEnumeratorBehavior`) when both traits compose into one enum.
     */
    public function cached(): bool
    {
        return Cache::has($this->key());
    }

    /**
     * Cache-or-compute. Calls `$callback` and stores the result under
     * this case's key when absent. Null TTL caches forever.
     */
    public function remember(Closure $callback, mixed $ttl = null): mixed
    {
        if ($ttl === null) {
            return Cache::rememberForever($this->key(), $callback);
        }

        return Cache::remember($this->key(), $ttl, $callback);
    }

    /**
     * Increment a numeric cached value (creating it at `$by` if absent).
     */
    public function increment(int $by = 1): int|bool
    {
        return Cache::increment($this->key(), $by);
    }

    /**
     * Decrement a numeric cached value.
     */
    public function decrement(int $by = 1): int|bool
    {
        return Cache::decrement($this->key(), $by);
    }
}
