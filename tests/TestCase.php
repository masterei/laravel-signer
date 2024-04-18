<?php

namespace Masterei\Signer\Tests;

use Masterei\Signer\SignerServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Automatically enables package discoveries.
     */
    protected $enablesPackageDiscoveries = true;

    /**
     * Get package providers.
     */
    protected function getPackageProviders($app)
    {
        return SignerServiceProvider::class;
    }

    /**
     * Get the application timezone.
     */
    protected function getApplicationTimezone($app): string
    {
        return 'Asia/Manila';
    }
}
