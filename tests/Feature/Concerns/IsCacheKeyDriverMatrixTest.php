<?php

declare(strict_types=1);

use Illuminate\Database\DatabaseManager;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Simtabi\Laranail\Enumerator\Concerns\IsCacheKey;
use Simtabi\Laranail\Enumerator\Contracts\Cacheable;

// PR-ξ — driver-matrix coverage for v0.3.0 IsCacheKey. The trait
// proxies to the Cache facade, so it should be driver-agnostic by
// construction. These tests pin that against the file driver and the
// database driver (the two drivers Testbench supports out of the
// box). Redis stays as a CI follow-up — it needs a Redis instance
// the local test harness can't reliably bind.
//
// The shared assertion set runs against each driver: put/get/cached/
// forget roundtrip + remember + increment/decrement. The drivers
// implement these via different machinery (file: serialised flat
// files in storage/framework/cache; database: row in `cache` table
// with `expiration` epoch), so a green matrix gives real confidence.

enum DriverMatrixCacheKey: string implements Cacheable
{
    use IsCacheKey;

    case User = 'user:current';
    case Tenant = 'tenant:config';
    case Counter = 'counter:visits';
}

/** Common roundtrip used per driver. */
function driverMatrixSharedRoundtrip(): void
{
    DriverMatrixCacheKey::User->forget();
    expect(DriverMatrixCacheKey::User->cached())->toBeFalse();

    // put + get + cached
    DriverMatrixCacheKey::User->put(['id' => 42, 'name' => 'Imani'], ttl: 60);
    expect(DriverMatrixCacheKey::User->cached())->toBeTrue();
    expect(DriverMatrixCacheKey::User->get())->toBe(['id' => 42, 'name' => 'Imani']);

    // get default when absent
    expect(DriverMatrixCacheKey::Tenant->get('fallback'))->toBe('fallback');

    // remember roundtrip
    $callCount = 0;
    $compute = function () use (&$callCount): string {
        $callCount++;

        return 'computed';
    };
    expect(DriverMatrixCacheKey::Tenant->remember($compute, ttl: 60))->toBe('computed');
    expect(DriverMatrixCacheKey::Tenant->remember($compute, ttl: 60))->toBe('computed');
    expect($callCount)->toBe(1);  // 2nd hit served from cache

    // forget
    DriverMatrixCacheKey::User->forget();
    expect(DriverMatrixCacheKey::User->cached())->toBeFalse();
}

beforeEach(function (): void {
    // Make sure each test starts from a clean slate.
    Cache::flush();
});

afterEach(function (): void {
    // Hard reset so file-driver leftovers don't bleed.
    Cache::flush();
});

/** Bring up the `cache` table on the in-memory sqlite connection. */
function driverMatrixEnsureCacheTable(): void
{
    if (! Schema::hasTable('cache')) {
        Schema::create('cache', function (Blueprint $table): void {
            $table->string('key')->primary();
            $table->mediumText('value');
            $table->bigInteger('expiration')->index();
        });
    }
}

// === File driver ===========================================================

it('IsCacheKey works against the file cache driver', function (): void {
    config()->set('cache.default', 'file');
    // Testbench ships the file driver pointing at vendor/orchestra/testbench-core/
    // laravel/storage/framework/cache/data. Ensure that path exists for the
    // test runner.
    $cachePath = storage_path('framework/cache/data');
    (new Filesystem)->ensureDirectoryExists($cachePath);

    driverMatrixSharedRoundtrip();
});

it('IsCacheKey increment + decrement work against the file driver', function (): void {
    config()->set('cache.default', 'file');
    (new Filesystem)->ensureDirectoryExists(storage_path('framework/cache/data'));

    DriverMatrixCacheKey::Counter->forget();
    DriverMatrixCacheKey::Counter->put(10, ttl: 60);

    expect(DriverMatrixCacheKey::Counter->increment())->toBe(11);
    expect(DriverMatrixCacheKey::Counter->increment(4))->toBe(15);
    expect(DriverMatrixCacheKey::Counter->decrement(5))->toBe(10);
    expect(DriverMatrixCacheKey::Counter->get())->toBe(10);
});

// === Database driver =======================================================

it('IsCacheKey works against the database cache driver', function (): void {
    driverMatrixEnsureCacheTable();
    config()->set('cache.default', 'database');

    driverMatrixSharedRoundtrip();

    // Spot-check: an underlying row IS in the `cache` table. The key
    // is configurably prefixed (default `laravel_cache_`) so look for
    // a key suffixed with the case's `key()` rather than the full
    // prefixed string.
    DriverMatrixCacheKey::User->put('marker-row', ttl: 60);
    $row = DB::table('cache')->where('key', 'like', '%user:current')->first();
    expect($row)->not->toBeNull();
})->skip(
    ! class_exists(DatabaseManager::class),
    'Database driver test requires Illuminate\Database — Testbench provides it.',
);

it('IsCacheKey increment + decrement work against the database driver', function (): void {
    driverMatrixEnsureCacheTable();
    config()->set('cache.default', 'database');

    DriverMatrixCacheKey::Counter->forget();
    DriverMatrixCacheKey::Counter->put(10, ttl: 60);

    expect(DriverMatrixCacheKey::Counter->increment())->toBe(11);
    expect(DriverMatrixCacheKey::Counter->increment(4))->toBe(15);
    expect(DriverMatrixCacheKey::Counter->decrement(5))->toBe(10);
    expect(DriverMatrixCacheKey::Counter->get())->toBe(10);
})->skip(
    ! class_exists(DatabaseManager::class),
    'Database driver test requires Illuminate\Database — Testbench provides it.',
);

// === Redis (deferred) =======================================================

it('IsCacheKey is documented to work against Redis (deferred to CI)', function (): void {
    // Pinning Redis behaviour locally would require a redis-server
    // sidecar the test harness can't reliably bind. The trait is
    // driver-agnostic by construction (it calls Cache::*), so we
    // accept the gap in v0.4.0 and revisit when a CI redis sidecar
    // is set up.
    expect(true)->toBeTrue();
})->skip('Redis cache driver test deferred — needs CI redis sidecar.');
