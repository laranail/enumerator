<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Tests;

use Livewire\LivewireServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;
use Simtabi\Laranail\Enumerator\EnumeratorServiceProvider;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        $providers = [
            EnumeratorServiceProvider::class,
        ];

        // Livewire is require-dev (per ADR-0006). When it's installed,
        // register its ServiceProvider so tests can use Livewire::test()
        // for real-roundtrip assertions. When it isn't, the
        // Integrations\Livewire\* tests just skip.
        if (class_exists(LivewireServiceProvider::class)) {
            $providers[] = LivewireServiceProvider::class;
        }

        return $providers;
    }

    protected function defineEnvironment($app): void
    {
        // Livewire encrypts component-state checksums; needs an APP_KEY
        // to do that. Fake a deterministic one for tests.
        $app['config']->set('app.key', 'base64:' . base64_encode(random_bytes(32)));

        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        $app['config']->set('enumerator.cache.driver', 'memory');
        $app['config']->set('enumerator.css_framework', 'plain');
    }

    protected function defineDatabaseMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }
}
