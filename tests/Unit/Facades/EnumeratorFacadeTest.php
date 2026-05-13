<?php

declare(strict_types=1);

use Simtabi\Laranail\Enumerator\Facades\Enumerator;
use Simtabi\Laranail\Enumerator\Support\AttributesOverrideResolver;
use Simtabi\Laranail\Enumerator\Support\EnumeratorRegistry;
use Simtabi\Laranail\Enumerator\Support\LayeredCache;

// Facade — resolves to the EnumeratorRegistry singleton.

it('cache() forwards to the registry singleton', function (): void {
    expect(Enumerator::cache())->toBeInstanceOf(LayeredCache::class);
});

it('overrides() forwards to the registry singleton', function (): void {
    expect(Enumerator::overrides())->toBeInstanceOf(AttributesOverrideResolver::class);
});

it('cache() returns the same instance as the bound registry', function (): void {
    $registry = app(EnumeratorRegistry::class);
    expect(Enumerator::cache())->toBe($registry->cache());
});

it('overrides() returns the same instance as the bound registry', function (): void {
    $registry = app(EnumeratorRegistry::class);
    expect(Enumerator::overrides())->toBe($registry->overrides());
});
