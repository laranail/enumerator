<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Modules\Lighthouse;

use Illuminate\Support\ServiceProvider;

/**
 * Optional Lighthouse cast module.
 *
 * Activates when both (a) Lighthouse is installed AND
 * (b) `config('enumerator.modules.lighthouse')` is true. Provides the
 * abstract `EnumScalar` that consumers extend per-enum.
 *
 * Distinct from `Modules/GraphQL/SchemaExporter` (Step 35) — that one
 * emits framework-agnostic schema text. This module hooks Lighthouse's
 * runtime resolver pipeline.
 */
final class LighthouseServiceProvider extends ServiceProvider
{
    private const LIGHTHOUSE_PROVIDER_CLASS = 'Nuwave\\Lighthouse\\LighthouseServiceProvider';

    public function register(): void
    {
        // No bindings — consumers extend EnumScalar per-enum.
    }

    public function boot(): void
    {
        if (! $this->shouldRegister()) {
            return;
        }

        // Lighthouse discovers scalar classes via its config. Nothing to
        // register here — consumers wire their EnumScalar subclasses to
        // their schema directly. This boot() exists as a hook for future
        // expansion (e.g. auto-discovery of scalars).
    }

    private function shouldRegister(): bool
    {
        if (! class_exists(self::LIGHTHOUSE_PROVIDER_CLASS)) {
            return false;
        }

        return (bool) $this->app['config']->get('enumerator.modules.lighthouse', false);
    }
}
