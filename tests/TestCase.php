<?php

namespace Masterei\Signer\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Masterei\Signer\SignerServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    /**
     * Automatically enables package discoveries.
     */
    protected $enablesPackageDiscoveries = true;

    /**
     * Get the application timezone.
     */
    protected function getApplicationTimezone($app): string
    {
        return 'Asia/Manila';
    }

    /**
     * Get package providers.
     */
    protected function getPackageProviders($app)
    {
        return SignerServiceProvider::class;
    }

    /**
     * Define database migrations.
     */
    protected function defineDatabaseMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/migrations');
    }

    /**
     * Define routes setup.
     */
    protected function defineRoutes(\Illuminate\Routing\Router $router): void
    {
        $router->get('test', [\Masterei\Signer\Tests\Controllers\TestController::class, 'successResponse'])
            ->name('test')
            ->middleware('signer');
    }
}
