<?php

namespace Silverd\OhMyLaravel;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        if ($this->app->runningInConsole()) {

            $basePath = base_path();

            $this->publishes([

                __DIR__ . '/../app'    => $basePath . '/app',
                __DIR__ . '/../routes' => $basePath . '/routes',
                __DIR__ . '/../config' => $basePath . '/config',
                __DIR__ . '/../public' => $basePath . '/public',

            ], 'oh-my-laravel');

            $this->commands([
                Console\Commands\InstallCommand::class,
            ]);
        }
    }

    public function register()
    {

    }
}
