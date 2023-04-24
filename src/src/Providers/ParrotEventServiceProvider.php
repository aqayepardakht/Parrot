<?php

namespace Aqayepardakht\Parrot\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider;
use Aqayepardakht\Parrot\Events\ParrotUnauthorized;
use Aqayepardakht\Parrot\Listeners\parrotUnauthorizedLog;

class ParrotEventServiceProvider extends EventServiceProvider {
    protected $listen = [
        ParrotUnauthorized::class => [
            parrotUnauthorizedLog::class
        ]
    ];

    public function boot() {
        parent::boot();
    }
}
