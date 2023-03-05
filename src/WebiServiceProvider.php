<?php

namespace Webi;

use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Http\Kernel;
use Webi\Http\Middleware\WebiChangeLocale;
use Webi\Http\Middleware\WebiJsonResponse;
use Webi\Providers\WebiEventServiceProvider;
use Webi\Providers\WebiResponseMacroServiceProvider;
use Webi\Services\Webi;

class WebiServiceProvider extends ServiceProvider
{
	public function register()
	{
		$this->mergeConfigFrom(__DIR__ . '/../config/config.php', 'webi');

		$this->app->bind('webi', function ($app) {
			return new Webi();
		});

		$this->app->register(WebiEventServiceProvider::class);
		$this->app->register(WebiResponseMacroServiceProvider::class);
	}

	public function boot(Kernel $kernel)
	{
		// Global
		// $kernel->pushMiddleware(GlobalMiddleware::class);

		// Router
		$this->app['router']->aliasMiddleware('webi-locale', WebiChangeLocale::class);
		$this->app['router']->aliasMiddleware('webi-json', WebiJsonResponse::class);

		// Create routes
		if (config('webi.settings.routes') == true) {
			$this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
		}

		$this->loadViewsFrom(__DIR__ . '/../resources/views', 'webi');
		$this->loadTranslationsFrom(__DIR__ . '/../lang', 'webi');
		$this->loadJsonTranslationsFrom(__DIR__ . '/../lang');
		$this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

		if ($this->app->runningInConsole()) {
			$this->publishes([
				__DIR__ . '/../config/config.php' => config_path('webi.php'),
			], 'webi-config');

			$this->publishes([
				__DIR__ . '/../resources/views' => resource_path('views/vendor/webi')
			], 'webi-email');

			$this->publishes([
				__DIR__ . '/../resources/logo' => public_path('vendor/webi/logo')
			], 'webi-public');

			$this->publishes([
				__DIR__ . '/../lang' => base_path('lang/vendor/webi'),
			], 'webi-lang');

			$this->publishes([
				__DIR__ . '/../lang/en' => base_path('lang/en')
			], 'webi-lang-en');

			$this->publishes([
				__DIR__ . '/../lang/pl' => base_path('lang/pl')
			], 'webi-lang-pl');
		}
	}
}
