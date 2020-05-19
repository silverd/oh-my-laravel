<?php

namespace Silverd\OhMyLaravel\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Silverd\OhMyLaravel\Extensions\Auth\AdvTokenGuard;
use Silverd\OhMyLaravel\Extensions\Auth\HttpBasicGuard;

class AuthServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // BigApiToken
        \Auth::extend('adv_token', function ($app, $name, array $config) {
            return new AdvTokenGuard(
                \Auth::createUserProvider($config['provider']),
                $app['request']
            );
        });

        // 简单版 Basic Auth
        \Auth::extend('http_basic', function ($app, $name, array $config) {
            return new HttpBasicGuard($app['request']);
        });
    }
}
