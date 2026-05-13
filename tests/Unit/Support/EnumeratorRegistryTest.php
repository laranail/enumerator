<?php

declare(strict_types=1);

use Illuminate\Config\Repository;
use Simtabi\Laranail\Enumerator\Support\AttributesOverrideResolver;
use Simtabi\Laranail\Enumerator\Support\EnumeratorRegistry;
use Simtabi\Laranail\Enumerator\Support\LayeredCache;

// EnumeratorRegistry — singleton holder for the cache + override resolver.

function buildRegistry(): EnumeratorRegistry
{
    return new EnumeratorRegistry(
        new LayeredCache('memory'),
        new AttributesOverrideResolver(new Repository(['enumerator' => ['overrides' => []]])),
    );
}

it('cache() returns the constructor-injected cache', function (): void {
    $cache = new LayeredCache('memory');
    $registry = new EnumeratorRegistry(
        $cache,
        new AttributesOverrideResolver(new Repository(['enumerator' => ['overrides' => []]])),
    );
    expect($registry->cache())->toBe($cache);
});

it('overrides() returns the constructor-injected resolver', function (): void {
    $overrides = new AttributesOverrideResolver(new Repository(['enumerator' => ['overrides' => []]]));
    $registry = new EnumeratorRegistry(new LayeredCache('memory'), $overrides);
    expect($registry->overrides())->toBe($overrides);
});

it('set() stores a singleton retrievable via instance()', function (): void {
    $registry = buildRegistry();
    EnumeratorRegistry::set($registry);
    expect(EnumeratorRegistry::instance())->toBe($registry);
});

it('reset() clears the singleton', function (): void {
    $registry = buildRegistry();
    EnumeratorRegistry::set($registry);
    EnumeratorRegistry::reset();
    expect(EnumeratorRegistry::instance())->toBeNull();
});
