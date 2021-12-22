<?php

namespace Aqayepardakht\Parrot\Listeners;

use Aqayepardakht\Parrot\Events\ParrotUnauthorized;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class parrotUnauthorizedLog {

    public function __construct() {}

    public function handle(ParrotUnauthorized $event) {
        // لاگ مشکل در ورود
    }
}
