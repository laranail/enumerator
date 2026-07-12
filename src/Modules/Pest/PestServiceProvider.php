<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Modules\Pest;

use Illuminate\Support\ServiceProvider;

/**
 * Optional Pest plugin module.
 *
 * Registers custom Pest expectations for enumerator cases on boot, but
 * only when (a) `pestphp/pest` is installed AND (b)
 * `config('enumerator.modules.pest')` is true. Both conditions failing
 * makes this provider a no-op.
 */
final class PestServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // No bindings — the module is a boot-time expectation registrar.
    }

    public function boot(): void
    {
        if (! $this->shouldRegister()) {
            return;
        }

        Expectations::register();
    }

    /**
     * Module activates when Pest is loaded AND the config toggle is true.
     */
    private function shouldRegister(): bool
    {
        if (! function_exists('expect')) {
            // Pest's `expect()` global function isn't present; we're not in
            // a Pest test run. Skip silently.
            return false;
        }

        return (bool) $this->app['config']->get('enumerator.modules.pest', false);
    }
}
