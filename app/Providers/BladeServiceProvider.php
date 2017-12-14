<?php

namespace App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class BladeServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        // Ref: https://www.youtube.com/watch?v=sqxOXnBbP-k&list=PLfdtiltiRHWG4xMZm1OL_wglxkBo8v_xN&index=14
        Blade::if('debug', function () {
            return config('app.debug');
        });
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
