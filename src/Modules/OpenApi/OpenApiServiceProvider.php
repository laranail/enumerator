<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Modules\OpenApi;

use Illuminate\Support\ServiceProvider;

/**
 * Optional OpenAPI 3.1 schema generator module.
 *
 * Activates when `config('enumerator.modules.openapi')` is true. Binds
 * `OpenApiSchemaExporter` as a singleton so callers can resolve it from
 * the container or DI it directly into commands / handlers.
 *
 * The `enumerator:export --openapi` flag (Step 25) consults this binding.
 * For now, the export command's `--openapi` handling lives outside this
 * module — the module just provides the exporter type.
 */
final class OpenApiServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        if (! $this->shouldRegister()) {
            return;
        }

        $this->app->singleton(OpenApiSchemaExporter::class);
    }

    public function boot(): void
    {
        // No boot work — the exporter is pure.
    }

    private function shouldRegister(): bool
    {
        return (bool) $this->app['config']->get('enumerator.modules.openapi', false);
    }
}
