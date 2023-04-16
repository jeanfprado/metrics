<?php

namespace Jeanfprado\Metric;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class MetricServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->configure();
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                \Jeanfprado\Metric\Console\MetricValueCommand::class,
            ]);
        }

        $this->loadViewsFrom(__DIR__.'/Views', 'metrics');


        $this->registerCarbonMacros();
        $this->registerRoutes();
        $this->registerPublishing();
    }

     /**
     * Register the Nova Carbon macros.
     *
     * @return void
     */
    protected function registerCarbonMacros()
    {
        Carbon::mixin(new Macros\FirstDayOfQuarter());
        Carbon::mixin(new Macros\FirstDayOfPreviousQuarter());
    }

     /**
     * Setup the configuration for Cashier.
     *
     * @return void
     */
    protected function configure()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/metrics.php',
            'metrics'
        );
    }

    /**
     * Register the package routes.
     *
     * @return void
     */
    protected function registerRoutes()
    {
        Route::group($this->routeConfiguration(), function () {
            $this->loadRoutesFrom(__DIR__ . '/../routes/api.php');
        });
    }

    /**
     * Get the Nova route group configuration array.
     *
     * @return array
     */
    protected function routeConfiguration()
    {
        return [
            'namespace' => 'Jeanfprado\Metric\Http\Controllers',
            'prefix' => 'metric-api',
            'middleware' => config('metrics.middleware', null),
        ];
    }

    /**
     * Register the package's publishable resources.
     *
     * @return void
     */
    protected function registerPublishing()
    {
        $this->publishes([
            __DIR__.'/../config/metrics.php' => $this->app->configPath('metrics.php'),
        ], 'metrics-config');
    }
}
