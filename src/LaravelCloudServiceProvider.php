<?php

namespace LaravelCloudConnector;

use Illuminate\Support\ServiceProvider;

class LaravelCloudServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/laravel-cloud.php',
            'laravel-cloud'
        );

        $this->app->singleton(LaravelCloudClient::class, function ($app) {
            return new LaravelCloudClient(
                apiToken: config('laravel-cloud.api_token'),
                baseUrl: config('laravel-cloud.base_url'),
                timeout: config('laravel-cloud.timeout'),
                retryTimes: config('laravel-cloud.retry.times'),
                retrySleep: config('laravel-cloud.retry.sleep'),
            );
        });

        $this->app->alias(LaravelCloudClient::class, 'laravel-cloud');
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/laravel-cloud.php' => config_path('laravel-cloud.php'),
            ], 'laravel-cloud-config');
        }
    }
}
