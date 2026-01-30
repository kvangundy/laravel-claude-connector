<?php

namespace LaravelCloudConnector\Tests;

use LaravelCloudConnector\LaravelCloudServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            LaravelCloudServiceProvider::class,
        ];
    }

    protected function getPackageAliases($app): array
    {
        return [
            'LaravelCloud' => \LaravelCloudConnector\Facades\LaravelCloud::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('laravel-cloud.api_token', 'test-token');
        $app['config']->set('laravel-cloud.base_url', 'https://cloud.laravel.com/api');
    }
}
