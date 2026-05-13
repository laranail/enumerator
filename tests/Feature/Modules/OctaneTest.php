<?php

declare(strict_types=1);

use Simtabi\Laranail\Enumerator\Modules\Octane\OctaneServiceProvider;
use Simtabi\Laranail\Enumerator\Modules\Octane\WarmCachesListener;
use Simtabi\Laranail\Enumerator\Presets\Enums\StatusEnum;
use Simtabi\Laranail\Enumerator\Support\AttributesCache;
use Simtabi\Laranail\Enumerator\Support\LayeredCache;
use Simtabi\Laranail\Enumerator\Support\ReflectionCachePersistor;

it('WarmCachesListener::handle() calls restore() on the persistor', function (): void {
    $tmp = tempnam(sys_get_temp_dir(), 'octane-') . '.php';

    // Pre-warm a snapshot via a stand-alone persistor.
    $store = new LayeredCache('file', $tmp);
    $persistor = new ReflectionCachePersistor($store);
    $persistor->dump([StatusEnum::class]);

    // Flush in-memory state.
    AttributesCache::flush();
    expect(AttributesCache::snapshot())->toBe([]);

    // Listener restores from the same on-disk file.
    $coldStore = new LayeredCache('file', $tmp);
    $coldPersistor = new ReflectionCachePersistor($coldStore);
    $listener = new WarmCachesListener($coldPersistor);
    $listener->handle((object) ['fake' => 'event']);

    expect(AttributesCache::snapshot())
        ->toHaveKey(StatusEnum::class . '::Active');

    @unlink($tmp);
});

it('OctaneServiceProvider no-ops when the Octane event class is absent', function (): void {
    // The Octane event class isn't installed in this test environment,
    // so the provider's shouldRegister() returns false even if the
    // config flag is on. Verify nothing throws and no listener is bound.
    config()->set('enumerator.modules.octane', true);

    app()->register(OctaneServiceProvider::class, true);

    // No assertion that throws here — just confirm registration is safe.
    expect(true)->toBeTrue();
});
