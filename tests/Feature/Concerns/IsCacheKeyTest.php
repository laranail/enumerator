<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Cache;
use Simtabi\Laranail\Enumerator\Concerns\HasEnumeratorBehavior;
use Simtabi\Laranail\Enumerator\Concerns\IsCacheKey;
use Simtabi\Laranail\Enumerator\Contracts\Cacheable;
use Simtabi\Laranail\Enumerator\Contracts\Enumerator;

// Feature coverage for the v0.3.0 PR-η IsCacheKey trait + Cacheable
// contract. Each test exercises one of the trait's wrappers around
// Laravel's Cache facade against the array driver bound by Testbench.

enum CacheKeyFixture: string implements Cacheable
{
    use IsCacheKey;

    case CurrentUser = 'user:current';
    case TenantConfig = 'tenant:config';
    case Counter = 'counter';
}

enum PureCacheKeyFixture implements Cacheable
{
    use IsCacheKey;

    case Foo;
    case Bar;
}

enum PrefixedCacheKey: string implements Cacheable
{
    use IsCacheKey;

    case ThemeColor = 'theme.color';

    public function key(): string
    {
        return 'settings:' . $this->value;
    }
}

// Regression fixture for the smoke-test landmine: HasEnumeratorBehavior
// ships a static `has(target)` membership check, IsCacheKey ships an
// instance presence probe. The trait composition would have raised a
// fatal trait-method collision had `IsCacheKey` named its presence
// probe `has()`. Renaming to `cached()` keeps both available.
enum ComposedCacheableEnum: string implements Cacheable, Enumerator
{
    use HasEnumeratorBehavior;
    use IsCacheKey;

    case Active = 'active';
    case Inactive = 'inactive';

    public function key(): string
    {
        return 'composed:' . $this->value;
    }
}

beforeEach(function (): void {
    Cache::flush();
});

it('key() returns the backed-enum value by default', function (): void {
    expect(CacheKeyFixture::CurrentUser->key())->toBe('user:current');
    expect(CacheKeyFixture::TenantConfig->key())->toBe('tenant:config');
});

it('key() returns the case name for pure enums', function (): void {
    expect(PureCacheKeyFixture::Foo->key())->toBe('Foo');
    expect(PureCacheKeyFixture::Bar->key())->toBe('Bar');
});

it('key() can be overridden in the consumer enum', function (): void {
    expect(PrefixedCacheKey::ThemeColor->key())->toBe('settings:theme.color');
});

it('put + get roundtrips a scalar value', function (): void {
    CacheKeyFixture::CurrentUser->put('hello');

    expect(CacheKeyFixture::CurrentUser->get())->toBe('hello');
});

it('put + get roundtrips an array value', function (): void {
    CacheKeyFixture::TenantConfig->put(['plan' => 'pro', 'seats' => 12]);

    expect(CacheKeyFixture::TenantConfig->get())->toBe(['plan' => 'pro', 'seats' => 12]);
});

it('get() returns the default when the key is absent', function (): void {
    expect(CacheKeyFixture::CurrentUser->get('fallback'))->toBe('fallback');
});

it('cached() reflects presence + absence', function (): void {
    expect(CacheKeyFixture::CurrentUser->cached())->toBeFalse();

    CacheKeyFixture::CurrentUser->put('x');
    expect(CacheKeyFixture::CurrentUser->cached())->toBeTrue();
});

it('forget() drops the cached value', function (): void {
    CacheKeyFixture::CurrentUser->put('x');
    expect(CacheKeyFixture::CurrentUser->cached())->toBeTrue();

    CacheKeyFixture::CurrentUser->forget();
    expect(CacheKeyFixture::CurrentUser->cached())->toBeFalse();
});

it('remember() runs the callback when absent + caches the result', function (): void {
    $callCount = 0;
    $callback = function () use (&$callCount): string {
        $callCount++;

        return 'computed';
    };

    $first = CacheKeyFixture::TenantConfig->remember($callback, ttl: 60);
    $second = CacheKeyFixture::TenantConfig->remember($callback, ttl: 60);

    expect($first)->toBe('computed');
    expect($second)->toBe('computed');
    expect($callCount)->toBe(1);  // callback ran once, second hit was cached
});

it('remember() with ttl=null caches forever', function (): void {
    $result = CacheKeyFixture::TenantConfig->remember(fn () => 'forever');

    expect($result)->toBe('forever');
    expect(CacheKeyFixture::TenantConfig->cached())->toBeTrue();
});

it('increment / decrement work with the underlying Cache facade', function (): void {
    CacheKeyFixture::Counter->put(10);

    expect(CacheKeyFixture::Counter->increment())->toBe(11);
    expect(CacheKeyFixture::Counter->increment(4))->toBe(15);
    expect(CacheKeyFixture::Counter->decrement(5))->toBe(10);
    expect(CacheKeyFixture::Counter->get())->toBe(10);
});

it('two cases use distinct keys', function (): void {
    CacheKeyFixture::CurrentUser->put('user-val');
    CacheKeyFixture::TenantConfig->put('tenant-val');

    expect(CacheKeyFixture::CurrentUser->get())->toBe('user-val');
    expect(CacheKeyFixture::TenantConfig->get())->toBe('tenant-val');
});

it('composes cleanly with HasEnumeratorBehavior — both has() and cached() reachable', function (): void {
    // static membership check (from HasEnumeratorBehavior)
    expect(ComposedCacheableEnum::has('active'))->toBeTrue();
    expect(ComposedCacheableEnum::has('not-a-case'))->toBeFalse();

    // instance presence probe (from IsCacheKey)
    expect(ComposedCacheableEnum::Active->cached())->toBeFalse();

    ComposedCacheableEnum::Active->put('payload');
    expect(ComposedCacheableEnum::Active->cached())->toBeTrue();

    // both surfaces share no state — the static check is independent
    // of whether the value is cached
    expect(ComposedCacheableEnum::has('active'))->toBeTrue();

    ComposedCacheableEnum::Active->forget();
    expect(ComposedCacheableEnum::Active->cached())->toBeFalse();
    expect(ComposedCacheableEnum::has('active'))->toBeTrue();
});
