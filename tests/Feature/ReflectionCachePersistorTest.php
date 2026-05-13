<?php

declare(strict_types=1);

use Simtabi\Laranail\Enumerator\Presets\Enums\StatusEnum;
use Simtabi\Laranail\Enumerator\Support\AttributesCache;
use Simtabi\Laranail\Enumerator\Support\CasesCache;
use Simtabi\Laranail\Enumerator\Support\LayeredCache;
use Simtabi\Laranail\Enumerator\Support\ReflectionCachePersistor;

// / snapshot-persistor round-trip.

beforeEach(function (): void {
    // Each test starts with fresh in-memory state.
    AttributesCache::flush();
    CasesCache::flush();
});

it('snapshot() returns the in-memory memo for AttributesCache', function (): void {
    $bag = AttributesCache::for(StatusEnum::Active);
    $snapshot = AttributesCache::snapshot();

    expect($snapshot)->toHaveKey(StatusEnum::class . '::Active');
    expect($snapshot[StatusEnum::class . '::Active'])->toHaveKeys([
        'label', 'color', 'icon', 'order', 'description', 'help', 'bit', 'meta', 'cssClasses',
    ]);
    expect($snapshot[StatusEnum::class . '::Active']['label'])->toBe($bag->label);
});

it('restore() rehydrates AttributesCache memo', function (): void {
    AttributesCache::restore([
        'Foo::Bar' => ['label' => 'Restored', 'color' => 'success'],
    ]);
    $bag = AttributesCache::snapshot()['Foo::Bar'];

    expect($bag['label'])->toBe('Restored');
    expect($bag['color'])->toBe('success');
});

it('persists and restores reflection cache through LayeredCache', function (): void {
    $tmp = tempnam(sys_get_temp_dir(), 'enum-cache-') . '.php';
    $store = new LayeredCache('file', $tmp);
    $persistor = new ReflectionCachePersistor($store);

    // Warm + dump.
    $persistor->dump([StatusEnum::class]);
    expect(file_exists($tmp))->toBeTrue();

    // Flush the in-memory memos completely.
    AttributesCache::flush();
    CasesCache::flush();
    expect(AttributesCache::snapshot())->toBe([]);

    // Restore from disk via a fresh LayeredCache (force cold read).
    $coldStore = new LayeredCache('file', $tmp);
    $coldPersistor = new ReflectionCachePersistor($coldStore);
    $hit = $coldPersistor->restore();

    expect($hit)->toBeTrue();
    expect(AttributesCache::snapshot())->toHaveKey(StatusEnum::class . '::Active');
    expect(AttributesCache::for(StatusEnum::Active)->label)->toBe('Active');

    @unlink($tmp);
});

it('restore() returns false when no snapshot is present', function (): void {
    $tmp = tempnam(sys_get_temp_dir(), 'enum-cache-empty-') . '.php';
    @unlink($tmp);  // ensure the file truly doesn't exist
    $persistor = new ReflectionCachePersistor(new LayeredCache('file', $tmp));

    expect($persistor->restore())->toBeFalse();
});

it('enumerator:cache command persists via the persistor', function (): void {
    // Configure auto_warm_classes; run the command; verify the disk
    // payload is non-empty AND contains the expected structure.
    $tmp = tempnam(sys_get_temp_dir(), 'enum-cache-cli-') . '.php';
    config()->set('enumerator.cache.driver', 'file');
    config()->set('enumerator.cache.file_path', $tmp);
    config()->set('enumerator.cache.auto_warm_classes', [StatusEnum::class]);

    // Rebind LayeredCache to pick up the new config.
    app()->forgetInstance(LayeredCache::class);
    app()->forgetInstance(ReflectionCachePersistor::class);

    $this->artisan('enumerator:cache')->assertSuccessful();

    expect(file_exists($tmp))->toBeTrue();
    /** @var array<string, mixed> $payload */
    $payload = require $tmp;
    expect($payload)->toHaveKey('reflection.attributes');
    expect($payload['reflection.attributes'])->toHaveKey(StatusEnum::class . '::Active');

    @unlink($tmp);
});
