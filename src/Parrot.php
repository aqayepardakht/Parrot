<?php

namespace Aqayepardakht\Parrot;

use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Route;

use Aqayepardakht\Parrot\Traits\ClientToken;
use Aqayepardakht\Http\Request;
use Aqayepardakht\Parrot\Events\ParrotFaildRequest;

class Parrot {
   public static function registerRoutes() {
        Route::any('/parrot', function() {
            $service = request()->service;
            $explode = explode('_', $service);
            $requestType = end($explode);
            array_pop($explode); 

            $serviceName = ucfirst(implode('', array_map('ucfirst', $explode))).($requestType === 'request' ? 'Response' : 'Request'); 
            $serviceName = "App\Parrots\\".$serviceName;

            $service = new $serviceName();

            return $service->answer();
        })->middleware('client');
   }
}
