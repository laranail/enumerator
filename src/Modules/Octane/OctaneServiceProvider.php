<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Modules\Octane;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\ServiceProvider;

/**
 * Optional Octane warmup module.
 *
 * Activates when both (a) Octane is installed (`\Laravel\Octane\Octane`
 * class exists) AND (b) `config('enumerator.modules.octane')` is true.
 * Registers `WarmCachesListener` as a listener for Octane's
 * `WorkerStarting` event.
 *
 * Per-request state isn't relevant here — the reflection caches are
 * process-global static memos. Worker boots restore the snapshot once;
 * subsequent requests on the same worker hit the warm memo.
 */
final class OctaneServiceProvider extends ServiceProvider
{
    /**
     * The Octane event class we listen on. Referenced as a string so
     * this provider loads cleanly when Octane isn't installed.
     */
    private const WORKER_STARTING_EVENT = 'Laravel\\Octane\\Events\\WorkerStarting';

    public function register(): void
    {
        // No bindings — WarmCachesListener resolves dependencies via DI.
    }

    public function boot(): void
    {
        if (! $this->shouldRegister()) {
            return;
        }

        /** @var Dispatcher $events */
        $events = $this->app->make(Dispatcher::class);
        $events->listen(self::WORKER_STARTING_EVENT, [WarmCachesListener::class, 'handle']);
    }

    private function shouldRegister(): bool
    {
        if (! class_exists(self::WORKER_STARTING_EVENT)) {
            return false;
        }

        return (bool) $this->app['config']->get('enumerator.modules.octane', false);
    }
}
