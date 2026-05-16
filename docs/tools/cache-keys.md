# Cache keys

Define an enum case AS a cache-key namespace, with shorthand methods
that proxy to Laravel's `Cache` facade. Introduced in v0.3.0.

## Setup

Implement `Contracts\Cacheable` and `use IsCacheKey`:

```php
namespace App\Cache;

use Simtabi\Laranail\Enumerator\Concerns\IsCacheKey;
use Simtabi\Laranail\Enumerator\Contracts\Cacheable;

enum CacheKey: string implements Cacheable
{
    use IsCacheKey;

    case CurrentUser   = 'user:current';
    case TenantConfig  = 'tenant:config';
    case BillingPlan   = 'billing:plan';
    case Counter       = 'counter:visits';
}
```

That's the whole setup. The trait reads the case's backing value (or
its name for pure enums) as the cache-key string.

## Usage

```php
CacheKey::CurrentUser->put($user, ttl: 3600);
CacheKey::CurrentUser->get();                   // → $user
CacheKey::CurrentUser->get('fallback-value');   // default when absent
CacheKey::CurrentUser->cached();                // → true
CacheKey::CurrentUser->forget();                // → drops

CacheKey::TenantConfig->remember(
    fn () => TenantConfig::fromDatabase(),
    ttl: 60 * 5,
);

CacheKey::Counter->increment();          // → 1 (created if absent)
CacheKey::Counter->increment(by: 5);     // → 6
CacheKey::Counter->decrement(by: 2);     // → 4
```

## Method reference

| Method | Returns | Backing call |
|---|---|---|
| `key()` | `string` | (computed, override-friendly) |
| `put(mixed $value, mixed $ttl = null)` | `bool` | `Cache::forever()` when `$ttl === null`, `Cache::put()` otherwise |
| `get(mixed $default = null)` | `mixed` | `Cache::get()` |
| `forget()` | `bool` | `Cache::forget()` |
| `cached()` | `bool` | `Cache::has()` |
| `remember(Closure $cb, mixed $ttl = null)` | `mixed` | `Cache::rememberForever()` or `Cache::remember()` |
| `increment(int $by = 1)` | `int\|bool` | `Cache::increment()` |
| `decrement(int $by = 1)` | `int\|bool` | `Cache::decrement()` |

TTL semantics match Laravel's `Cache` facade exactly:

- `ttl = null` → store forever
- `ttl = int` → seconds
- `ttl = DateTimeInterface | DateInterval` → flow through unchanged

## Custom key shape

Override `key()` for a non-trivial namespace:

```php
enum SettingsKey: string implements Cacheable
{
    use IsCacheKey;

    case ThemeColor = 'theme.color';
    case Locale     = 'locale';

    public function key(): string
    {
        return 'settings:' . $this->value;
    }
}

SettingsKey::ThemeColor->put('#0066ff');
// stores under cache key "settings:theme.color"
```

The trait's `put` / `get` / `forget` / etc. always call `$this->key()`
— overriding it changes every method without re-declaring them.

## Implementing `Cacheable` directly

For one-off cases where you don't want the full trait, implement
`Cacheable` and use the Cache facade yourself:

```php
enum FeatureFlag: string implements Cacheable
{
    case DarkMode = 'dark_mode';

    public function key(): string
    {
        return 'feature:' . $this->value;
    }
}

Cache::put(FeatureFlag::DarkMode->key(), true);
Cache::has(FeatureFlag::DarkMode->key());
```

## Composing with `HasEnumerator`

`IsCacheKey` is a **standalone** trait — it's NOT in the
`HasEnumerator` umbrella. A pure cache-key enum that doesn't need
labels / colours / icons can use just this trait. A status enum
that ALSO serves as a cache key can use both:

```php
enum UserStatus: string implements Enumerator, Cacheable
{
    use HasEnumerator;   // labels, colors, etc.
    use IsCacheKey;      // cache-key methods

    case Active   = 'active';
    case Inactive = 'inactive';

    public function key(): string
    {
        return 'user:status:' . $this->value;
    }
}

$user->status->label();           // "Active"
UserStatus::Active->put('hello'); // caches under "user:status:active"
UserStatus::Active->cached();     // → true
UserStatus::has('active');        // static membership check — also true
```

The instance probe is `cached()` (not `has()`) so it doesn't collide
with the static `Enumerator::has(target)` membership check that
`HasEnumeratorBehavior` ships. Both traits compose cleanly without
trait-conflict resolution.

## Driver agnostic

The trait calls `Cache::*` so any driver Laravel supports works:
array (testing), file (default in v0.1+ Laravel skeletons), Redis,
Memcached, database, dynamodb. The package's tests run against the
array driver but no driver-specific code lives in the trait.

## See also

- [Contracts](contracts.md) — `Cacheable` marker contract reference
- [Concerns](concerns.md) — overview of all v0.3.0 traits
