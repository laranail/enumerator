<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Modules\GraphQL;

use Illuminate\Support\ServiceProvider;

/**
 * Optional GraphQL schema generator module.
 *
 * Framework-agnostic — does NOT require Lighthouse. Activates when
 * `config('enumerator.modules.graphql')` is true. Binds `SchemaExporter`
 * as a singleton so callers can resolve it from the container or DI it
 * into commands / handlers.
 *
 * Distinct from `Modules/Lighthouse/EnumScalar` (Step 26): that module
 * targets Lighthouse's runtime scalar registration. This one emits
 * portable `.graphql` text usable in any GraphQL stack.
 */
final class GraphQLServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        if (! $this->shouldRegister()) {
            return;
        }

        $this->app->singleton(SchemaExporter::class);
    }

    public function boot(): void
    {
        // No boot work — the exporter is pure.
    }

    private function shouldRegister(): bool
    {
        return (bool) $this->app['config']->get('enumerator.modules.graphql', false);
    }
}
