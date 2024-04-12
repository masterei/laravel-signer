<?php

namespace Masterei\Signer\Tests;

use Faker\Factory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Masterei\Signer\SignerServiceProvider;
use Masterei\Signer\Tests\Database\Factories\UserFactory;
use Masterei\Signer\Tests\Models\User;
use Masterei\Signer\Tests\Providers\RouteServiceProvider;
use Orchestra\Testbench\Attributes\WithMigration;
use Orchestra\Testbench\TestCase as BaseTestCase;

#[WithMigration]
abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    /**
     * Automatically enables package discoveries.
     */
    protected $enablesPackageDiscoveries = true;

    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {

        UserFactory::new()->count(10)->create();
    }

    /**
     * Get package providers.
     */
    protected function getPackageProviders($app): array
    {
        return [
            RouteServiceProvider::class,
            SignerServiceProvider::class,
        ];
    }

    /**
     * Get the application timezone.
     */
    protected function getApplicationTimezone($app): string
    {
        return 'Asia/Manila';
    }

    /**
     * Define database migrations.
     */
    protected function defineDatabaseMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/database/migrations');
    }
}
