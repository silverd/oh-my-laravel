<?php

namespace Silverd\OhMyLaravel\Providers;

use Laravel\Horizon\Horizon;
use Illuminate\Support\Facades\Gate;
use Laravel\Horizon\HorizonApplicationServiceProvider;

class HorizonServiceProvider extends HorizonApplicationServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        Horizon::routeMailNotificationsTo(config('logging.alarm_email'));
        Horizon::night();
    }

    protected function authorization()
    {
        // Horizon 权限交由 HttpBasicGuard 来守护
        Horizon::auth(function ($request) {
            return true;
        });
    }
}
