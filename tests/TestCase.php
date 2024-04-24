<?php

namespace Masterei\Signer\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Masterei\Signer\SignerServiceProvider;
use Orchestra\Testbench\Attributes\WithMigration;
use Orchestra\Testbench\TestCase as BaseTestCase;

//#[WithMigration]
abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withFactories(__DIR__ . '/database/factories');
    }

    /**
     * Automatically enables package discoveries.
     */
    protected $enablesPackageDiscoveries = true;

    /**
     * Get package providers.
     */
    protected function getPackageProviders($app)
    {
        return [
            SignerServiceProvider::class
        ];
    }

    /**
     * Define migrations that are only used for testing purposes and not part of the package.
     */
    protected function defineDatabaseMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }

    /**
     * Define routes setup.
     */
    protected function defineRoutes($router): void
    {
        $router->get('test', fn() => response(['message' => 'authorized'])->setStatusCode(200))
            ->name('test')
            ->middleware('signer');

        $router->get('test-relative', fn() => response(['message' => 'authorized'])->setStatusCode(200))
            ->name('test.relative')
            ->middleware('signer:relative');

        $router->get('test-strict', fn() => response(['message' => 'authorized'])->setStatusCode(200))
            ->name('test.strict')
            ->middleware('signer:strict');
    }
}
