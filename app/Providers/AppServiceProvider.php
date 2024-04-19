<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Events\MqttEvent;
use App\Events\LocationReceiveCreatedEvent;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        new LocationReceiveCreatedEvent();
        new MqttEvent();
       
    }
}
