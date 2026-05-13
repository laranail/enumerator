<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Simtabi\Laranail\Enumerator\EnumeratorServiceProvider;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            EnumeratorServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
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
