<?php

namespace App\Providers;

use Authy\AuthyApi;
use Illuminate\Support\ServiceProvider;

class AuthyApiProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(AuthyApi::class, function ($app) {
            $authyKey = env('AUTHY_API_KEY');

            return new AuthyApi($authyKey);

        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}