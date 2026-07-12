<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Modules\Octane;

use Simtabi\Laranail\Enumerator\Support\ReflectionCachePersistor;

/**
 * Octane worker boot listener
 *
 * Restores the persisted reflection cache snapshot (from
 * `enumerator:cache`) once per worker boot, so the worker doesn't have
 * to do reflection on the first request that touches each enum.
 *
 * Registered by `OctaneServiceProvider::boot()` when the module is
 * enabled, as a listener for `Laravel\Octane\Events\WorkerStarting`.
 * The event argument is untyped to keep this class loadable in
 * environments where Octane isn't installed.
 */
final class WarmCachesListener
{
    public function __construct(
        private readonly ReflectionCachePersistor $persistor,
    ) {}

    /**
     * Handle the `WorkerStarting` event. Idempotent — `restore()`
     * returns false if no snapshot is present, and we treat that as a
     * harmless cold start.
     */
    public function handle(object $event): void
    {
        unset($event);  // event payload unused — we only care that a worker is starting
        $this->persistor->restore();
    }
}
