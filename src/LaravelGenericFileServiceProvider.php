<?php namespace Ideil\LaravelGenericFile;

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
	 *
	 * @return void
	 */
	public function boot()
	{
		// config
		$this->publishes([
			__DIR__ . '/config/handlers-base.php' => config_path('generic-file/handlers-base.php'),
			__DIR__ . '/config/handlers-filters.php' => config_path('generic-file/handlers-filters.php'),
			__DIR__ . '/config/http.php' => config_path('generic-file/http.php'),
			__DIR__ . '/config/store.php' => config_path('generic-file/store.php'),
		]);

	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->app->singleton('generic-file', function($app)
		{
			$config = config('generic-file');

			if (empty($config))
			{
				throw new Exception('LaravelGenericFile not configured. Please run "php artisan vendor:publish"');
			}

			return new GenericFile($config);
		});
	}

}
