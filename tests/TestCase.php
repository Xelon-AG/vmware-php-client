<?php

namespace Xelon\VmWareClient\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Orchestra\Testbench\TestCase as Orchestra;
use Xelon\VmWareClient\VmWareClientServiceProvider;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function getPackageProviders($app)
    {
        return [
            VmWareClientServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        // Set up environment configuration for testing
        $app['config']->set('vmware-php-client.session_ttl', 30);
    }
}