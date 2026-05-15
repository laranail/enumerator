<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Cache;
use Simtabi\Laranail\Enumerator\Concerns\IsCacheKey;
use Simtabi\Laranail\Enumerator\Contracts\Cacheable;

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

it('has() reflects presence + absence', function (): void {
    expect(CacheKeyFixture::CurrentUser->has())->toBeFalse();

    CacheKeyFixture::CurrentUser->put('x');
    expect(CacheKeyFixture::CurrentUser->has())->toBeTrue();
});

it('forget() drops the cached value', function (): void {
    CacheKeyFixture::CurrentUser->put('x');
    expect(CacheKeyFixture::CurrentUser->has())->toBeTrue();

    CacheKeyFixture::CurrentUser->forget();
    expect(CacheKeyFixture::CurrentUser->has())->toBeFalse();
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
    expect(CacheKeyFixture::TenantConfig->has())->toBeTrue();
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
