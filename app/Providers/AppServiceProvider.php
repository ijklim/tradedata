<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Register Bootstrap 4 Form elements
        // 'components.form.text' refers to resources/views/components/form/text.blade.php
        // e.g. Form::bootstrapText('symbol', null, ['placeholder' => 'symbol, e.g. QQQ'])
        \Form::component('bootstrapText', 'components.form.text', ['name', 'value', 'attributes']);
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
