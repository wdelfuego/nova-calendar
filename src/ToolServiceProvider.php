<?php

/*
 * © Copyright 2022 · Willem Vervuurt, Studio Delfuego
 * © Copyright 2022 · Christophe Francey
 * 
 * You can modify, use and distribute this package under one of two licenses:
 * 1. GNU AGPLv3
 * 2. A perpetual, non-revocable and 100% free (as in beer) do-what-you-want 
 *    license that allows both non-commercial and commercial use, under conditions.
 *    See LICENSE.md for details.
 * 
 *    (it boils down to: do what you want as long as you're building and/or
 *     using calendar views, but don't embed this package or a modified version
 *     of it in free or paid-for software libraries and packages aimed at developers).
 */
 
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

        $this->publishes([
            __DIR__.'/../config/nova-calendar.php' => config_path('nova-calendar.php'),
        ], 'config');
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

        foreach(config('nova-calendar', []) as $calendarKey => $calendarConfig)
        {
            if(!isset($calendarConfig['uri']))
            {
                throw new \Exception("Missing calendar config option 'uri' for calendar $calendarKey in config/nova-calendar.php");
            }
                
            Nova::router(['nova', Authorize::class], $calendarConfig['uri'])
                ->group(__DIR__.'/../routes/inertia.php');

            Route::middleware(['nova', Authorize::class])
                ->prefix('nova-vendor/wdelfuego/nova-calendar/' .$calendarConfig['uri'])
                ->group(__DIR__.'/../routes/api.php');
        }

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
