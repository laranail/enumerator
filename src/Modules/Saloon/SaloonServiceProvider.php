<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Modules\Saloon;

use Illuminate\Support\ServiceProvider;

/**
 * Optional Saloon caster module.
 *
 * Activates when `config('enumerator.modules.saloon')` is true. The
 * `EnumCaster` utility has no Saloon SDK dependency (it's plain PHP that
 * walks arrays + serializes enums), so the module is mostly a discovery
 * hook — it binds the caster as a singleton for resolution from the
 * container.
 */
final class SaloonServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        if (! $this->shouldRegister()) {
            return;
        }

        $this->app->singleton(EnumCaster::class);
    }

    public function boot(): void
    {
        // No boot work.
    }

    private function shouldRegister(): bool
    {
        return (bool) $this->app['config']->get('enumerator.modules.saloon', false);
    }
}
