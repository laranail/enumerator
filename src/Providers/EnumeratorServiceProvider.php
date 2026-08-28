<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Validator;
use Simtabi\Laranail\Enumerator\Rules\EnumIn;
use Simtabi\Laranail\Enumerator\Rules\EnumName;
use Simtabi\Laranail\Enumerator\Rules\EnumNotIn;
use Simtabi\Laranail\Enumerator\Rules\EnumValue;
use Simtabi\Laranail\Enumerator\Rules\EnumTransition;
use Simtabi\Laranail\Enumerator\Support\LayeredCache;
use Simtabi\Laranail\Enumerator\Blade\BladeDirectives;
use Simtabi\Laranail\Enumerator\Blade\Components\Badge;
use Simtabi\Laranail\Enumerator\Contracts\TenantContext;
use Simtabi\Laranail\Enumerator\Blade\Components\Listing;
use Simtabi\Laranail\Enumerator\Console\IdeHelperCommand;
use Simtabi\Laranail\Enumerator\Blade\Components\Dropdown;
use Simtabi\Laranail\Enumerator\Support\NullTenantContext;
use Simtabi\Laranail\Enumerator\Support\EnumeratorRegistry;
use Simtabi\Laranail\Enumerator\Blade\Components\Checkboxes;
use Simtabi\Laranail\Enumerator\Contracts\TranslatorAdapter;
use Simtabi\Laranail\Enumerator\Console\MakeEnumeratorCommand;
use Simtabi\Laranail\Enumerator\Console\CacheEnumeratorCommand;
use Simtabi\Laranail\Enumerator\Console\ExportEnumeratorCommand;
use Simtabi\Laranail\Enumerator\Support\ReflectionCachePersistor;
use Simtabi\Laranail\Enumerator\Console\AnnotateEnumeratorCommand;
use Simtabi\Laranail\Enumerator\Support\AttributesOverrideResolver;
use Simtabi\Laranail\Enumerator\Console\CacheClearEnumeratorCommand;
use Simtabi\Laranail\Enumerator\Translations\LaravelTranslatorAdapter;
use Simtabi\Laranail\Enumerator\Modules\Pest\Providers\PestServiceProvider;
use Simtabi\Laranail\Enumerator\Modules\Octane\Providers\OctaneServiceProvider;
use Simtabi\Laranail\Enumerator\Modules\Saloon\Providers\SaloonServiceProvider;
use Simtabi\Laranail\Enumerator\Modules\GraphQL\Providers\GraphQLServiceProvider;
use Simtabi\Laranail\Enumerator\Modules\OpenApi\Providers\OpenApiServiceProvider;
use Simtabi\Laranail\Enumerator\Modules\Lighthouse\Providers\LighthouseServiceProvider;
use Simtabi\Laranail\Enumerator\Modules\StructuredOutput\Providers\StructuredOutputServiceProvider;

final class EnumeratorServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../../config/enumerator.php', 'laranail.enumerator');

        $this->app->singleton(LayeredCache::class, function ($app): LayeredCache {
            /** @var array{driver?: string, file_path?: ?string} $cfg */
            $cfg = (array) $app['config']->get('laranail.enumerator.cache', []);
            $driver = (string) ($cfg['driver'] ?? 'layered');
            $path = $cfg['file_path'] ?? null;
            if ($path === null && in_array($driver, ['file', 'layered'], true)) {
                $path = $app->bootstrapPath('cache/enumerator.php');
            }

            return new LayeredCache($driver, $path);
        });

        // TenantContext binding. Default = NullTenantContext (single-tenant).
        // Consumers swap via config('laranail.enumerator.tenancy.driver') = FQCN.
        $this->app->singleton(TenantContext::class, function ($app): TenantContext {
            $configured = $app['config']->get('laranail.enumerator.tenancy.driver');
            if (is_string($configured) && class_exists($configured)) {
                $instance = $app->make($configured);
                if ($instance instanceof TenantContext) {
                    return $instance;
                }
            }

            return new NullTenantContext;
        });

        $this->app->singleton(AttributesOverrideResolver::class, function ($app): AttributesOverrideResolver {
            // TenantContext is NOT injected at construction time —
            // AttributesOverrideResolver resolves it lazily from the
            // container so tests can rebind via app()->instance() and
            // production swaps via config('laranail.enumerator.tenancy.driver')
            // both work without needing to flush this singleton.
            return new AttributesOverrideResolver($app['config']);
        });

        $this->app->singleton(EnumeratorRegistry::class, function ($app): EnumeratorRegistry {
            $registry = new EnumeratorRegistry(
                $app->make(LayeredCache::class),
                $app->make(AttributesOverrideResolver::class),
            );
            EnumeratorRegistry::set($registry);

            return $registry;
        });

        // Pluggable translator adapter. Default = LaravelTranslatorAdapter.
        // Consumers swap via config('laranail.enumerator.translator.adapter').
        $this->app->singleton(TranslatorAdapter::class, function ($app): TranslatorAdapter {
            $configured = $app['config']->get('laranail.enumerator.translator.adapter');
            if (is_string($configured) && class_exists($configured)) {
                $instance = $app->make($configured);
                if ($instance instanceof TranslatorAdapter) {
                    return $instance;
                }
            }

            return new LaravelTranslatorAdapter;
        });

        // Snapshot persistor bridges the reflection caches to LayeredCache.
        $this->app->singleton(ReflectionCachePersistor::class, function ($app): ReflectionCachePersistor {
            return new ReflectionCachePersistor($app->make(LayeredCache::class));
        });

        // Optional module providers. Each one no-ops unless its config
        // toggle is true (and any vendor marker class is present).
        // Registering unconditionally is safe — modules gate themselves.
        $this->app->register(PestServiceProvider::class);
        $this->app->register(OpenApiServiceProvider::class);
        $this->app->register(GraphQLServiceProvider::class);
        $this->app->register(SaloonServiceProvider::class);
        $this->app->register(OctaneServiceProvider::class);
        $this->app->register(LighthouseServiceProvider::class);
        $this->app->register(StructuredOutputServiceProvider::class);
    }

    public function boot(): void
    {
        // Ensure registry singleton is constructed so static accessors work.
        $this->app->make(EnumeratorRegistry::class);

        // Restore the reflection-cache snapshot at boot when auto_warm
        // is enabled (and the persisted file exists). No-op when the
        // memory driver is in use, no snapshot exists, or auto_warm is off.
        if ((bool) $this->app['config']->get('laranail.enumerator.cache.auto_warm', false)) {
            $this->app->make(ReflectionCachePersistor::class)->restore();
        }

        $this->registerValidationBridge();
        $this->bootResources();
        $this->bootViewComponents();
        $this->bootBladeDirectives();
        $this->bootValidationRules();

        if ($this->app->runningInConsole()) {
            $this->bootPublishing();
            $this->bootCommands();
        }
    }

    private function bootResources(): void
    {
        $this->loadTranslationsFrom(__DIR__ . '/../../lang', 'laranail-enumerator');
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');

        $viewNamespace = (string) ($this->app['config']->get('laranail.enumerator.view_namespace') ?? 'laranail-enumerator');
        $this->loadViewsFrom(__DIR__ . '/../../resources/views', $viewNamespace);
    }

    private function bootViewComponents(): void
    {
        $viewNamespace = (string) ($this->app['config']->get('laranail.enumerator.view_namespace') ?? 'laranail-enumerator');

        // `componentNamespace()` powers the `<x-{namespace}::{name}>` form by
        // auto-resolving classes inside a PHP namespace. Class basenames are
        // kebab-cased: Badge → badge, Listing → listing, Checkboxes →
        // checkboxes, Dropdown → dropdown, etc.
        Blade::componentNamespace(
            'Simtabi\\Laranail\\Enumerator\\Blade\\Components',
            $viewNamespace,
        );
    }

    private function bootBladeDirectives(): void
    {
        BladeDirectives::register();
    }

    private function bootValidationRules(): void
    {
        Validator::extend('enum_value', static function (string $attribute, mixed $value, array $parameters): bool {
            $class = $parameters[0] ?? null;
            if (! is_string($class) || ! enum_exists($class)) {
                return false;
            }

            $failed = false;
            (new EnumValue($class))
                ->validate($attribute, $value, static function () use (&$failed): void {
                    $failed = true;
                });

            return ! $failed;
        });
    }

    /**
     * Publish tags are a global registry too, and a bare `enumerator-config` is
     * a name any package or application could also claim — `vendor:publish
     * --tag=enumerator-config` would then write whichever registered last.
     */
    private function bootPublishing(): void
    {
        $this->publishes([
            __DIR__ . '/../../config/enumerator.php' => config_path('laranail/enumerator.php'),
        ], 'laranail::enumerator-config');

        // vendor/laranail-enumerator, matching the namespace registered by
        // loadTranslationsFrom(). Laravel looks for published overrides under
        // lang/vendor/{namespace}, so `vendor/enumerator` put them where nothing
        // reads them and every override was silently ignored.
        $this->publishes([
            __DIR__ . '/../../lang' => $this->app->langPath('vendor/laranail-enumerator'),
        ], 'laranail::enumerator-lang');

        $this->publishes([
            __DIR__ . '/../../resources/views' => $this->app->resourcePath('views/vendor/laranail-enumerator'),
        ], 'laranail::enumerator-views');

        $this->publishes([
            __DIR__ . '/../../resources/stubs' => $this->app->resourcePath('stubs/laranail-enumerator'),
        ], 'laranail::enumerator-stubs');

        $this->publishes([
            __DIR__ . '/../../database/migrations' => $this->app->databasePath('migrations'),
        ], 'laranail::enumerator-migrations');

        // Alpine.js bundle for the Alpine-enhanced components. Consumers
        // publish this once if they want the local-fallback path of
        // <x-laranail-enumerator::alpine-loader /> to work offline /
        // under strict CSP. See docs/tools/alpine-loader.md.
        $this->publishes([
            __DIR__ . '/../../resources/js' => $this->app->publicPath('vendor/laranail-enumerator'),
        ], 'laranail::enumerator-js');

        $this->publishes([
            __DIR__ . '/../Presets' => $this->app->path('Enums'),
        ], 'laranail::enumerator-presets');
    }

    private function bootCommands(): void
    {
        $this->commands([
            MakeEnumeratorCommand::class,
            AnnotateEnumeratorCommand::class,
            ExportEnumeratorCommand::class,
            IdeHelperCommand::class,
            CacheEnumeratorCommand::class,
            CacheClearEnumeratorCommand::class,
        ]);
    }

    /**
     * Surface this package's rules through `laranail/validation`, if it is here.
     *
     * One-way and guarded: validation is not a dependency -- this package stays
     * on PHP ^8.3 and validation is ^8.5 -- so the bridge is skipped entirely
     * when the class is absent, and the rules work exactly as before.
     *
     * Registration goes through `RuleRegistrar::register()` with a class-string
     * and an alias factory, NOT through the container tag. The tag is resolved
     * by `RuleRegistrar::classes()`, which instantiates each tagged service with
     * no arguments -- and every rule here requires the enum class it validates
     * against, so tagging them would either fail to construct or silently
     * produce a rule bound to nothing.
     *
     * Deferred to `booted()` so the ordering of the two providers does not
     * matter: by then validation has bound the registrar if it is installed.
     */
    private function registerValidationBridge(): void
    {
        $registrar = 'Simtabi\\Laranail\\Validation\\Support\\RuleRegistrar';

        if (! class_exists($registrar)) {
            return;
        }

        $this->app->booted(static function ($app) use ($registrar): void {
            $app->make($registrar)
                ->register(EnumValue::class, 'laranail_enum_value', static fn (array $p): object => new EnumValue(...$p))
                ->register(EnumName::class, 'laranail_enum_name', static fn (array $p): object => new EnumName(...$p))
                ->register(EnumIn::class, 'laranail_enum_in', static fn (array $p): object => new EnumIn(array_shift($p), $p))
                ->register(EnumNotIn::class, 'laranail_enum_not_in', static fn (array $p): object => new EnumNotIn(array_shift($p), $p))
                ->register(EnumTransition::class, 'laranail_enum_transition', static fn (array $p): object => new EnumTransition(...$p));
        });
    }
}
