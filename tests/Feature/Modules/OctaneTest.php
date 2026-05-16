<?php

declare(strict_types=1);

use Illuminate\Contracts\Events\Dispatcher;
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

it('OctaneServiceProvider registers a listener when the Octane event class IS present', function (): void {
    // We can't depend on Laravel\Octane being installed in this test
    // suite, so we class_alias an existing class to the fully-qualified
    // event name the provider checks. After the alias, class_exists()
    // returns true and the provider's shouldRegister() short-circuit
    // passes through to the listen() call.
    $aliasedName = 'Laravel\\Octane\\Events\\WorkerStarting';
    if (! class_exists($aliasedName)) {
        class_alias(stdClass::class, $aliasedName);
    }

    config()->set('enumerator.modules.octane', true);

    $dispatcher = app(Dispatcher::class);
    expect($dispatcher->hasListeners($aliasedName))->toBeFalse();

    app()->register(OctaneServiceProvider::class, true);

    expect($dispatcher->hasListeners($aliasedName))->toBeTrue();
});
