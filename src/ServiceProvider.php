<?php

namespace Silverd\OhMyLaravel;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    public function boot()
    {
        $this->registerPublishing();
    }

    public function register()
    {

    }

    protected function registerPublishing()
    {
        if ($this->app->runningInConsole()) {


            $this->publishes([

                __DIR__ . '/../app'    => app_path(),
                __DIR__ . '/../config' => config_path(),
                __DIR__ . '/../public' => public_path(),

            ], 'oh-my-laravel');
        }
    }
}
