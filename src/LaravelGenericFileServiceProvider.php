<?php

namespace Ideil\LaravelGenericFile;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Exception;

class LaravelGenericFileServiceProvider extends BaseServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Bootstrap the application events.
     */
    public function boot()
    {
        $configurations = [
            'handlers-base',
            'handlers-filters',
            'http',
            'store',
        ];

        $configDir = __DIR__.'/config';

        foreach ($configurations as $configName) {
            $this->publishes(["{$configDir}/{$configName}.php" => config_path("generic-file/{$configName}.php")]);
            $this->mergeConfigFrom("{$configDir}/{$configName}.php", 'generic-file.' . $configName);
        }
    }

    /**
     * Register the service provider.
     */
    public function register()
    {
        $this->app->singleton('generic-file', function ($app) {
            $config = config('generic-file');

            if (empty($config)) {
                throw new Exception('LaravelGenericFile not configured. Please run "php artisan vendor:publish"');
            }

            return new GenericFile($config);
        });
    }
}
