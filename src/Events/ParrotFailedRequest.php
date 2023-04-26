<?php

namespace Aqayepardakht\Parrot\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Aqayepardakht\Parrot\Service;
use Aqayepardakht\Http\Response;

class ParrotFailedRequest {
    use Dispatchable, InteractsWithSockets, SerializesModels;


    public function __construct(public Service $service, public Response $response) {}
}