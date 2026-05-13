<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Modules\StructuredOutput;

use Illuminate\Support\ServiceProvider;

/**
 * Optional structured-output schemas module.
 *
 * Activates when `config('enumerator.modules.structured_output')` is
 * true. Binds three sibling emitters as singletons — one each for
 * OpenAI, Anthropic, and MCP. No vendor SDK dependency at the file
 * level; consumers paste the schema fragments into their own client
 * calls.
 */
final class StructuredOutputServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        if (! $this->shouldRegister()) {
            return;
        }

        $this->app->singleton(OpenAiSchemaEmitter::class);
        $this->app->singleton(AnthropicSchemaEmitter::class);
        $this->app->singleton(McpSchemaEmitter::class);
    }

    public function boot(): void
    {
        // No boot work — emitters are pure.
    }

    private function shouldRegister(): bool
    {
        return (bool) $this->app['config']->get('enumerator.modules.structured_output', false);
    }
}
