<?php

namespace Wdelfuego\NovaCalendar;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Carbon;

use Laravel\Nova\Events\ServingNova;
use Laravel\Nova\Nova;

use Wdelfuego\NovaCalendar\Http\Middleware\Authorize;
use Wdelfuego\NovaCalendar\Console\Commands\CreateDefaultCalendarDataProvider;

class ToolServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->app->booted(function () {
            $this->routes();
        });

        Nova::serving(function (ServingNova $event) {
            Nova::provideToScript([
            ]);
        });
        
        if ($this->app->runningInConsole()) {
            $this->commands([
                CreateDefaultCalendarDataProvider::class,
            ]);
        }
    }

    /**
     * Register the tool's routes.
     *
     * @return void
     */
    protected function routes()
    {
        if ($this->app->routesAreCached()) {
            return;
        }

        Nova::router(['nova', Authorize::class], 'wdelfuego/nova-calendar')
            ->group(__DIR__.'/../routes/inertia.php');

        Route::middleware(['nova', Authorize::class])
            ->prefix('nova-vendor/wdelfuego/nova-calendar')
            ->group(__DIR__.'/../routes/api.php');
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {

    }
}
