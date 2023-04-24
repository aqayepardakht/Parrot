<?php

namespace Aqayepardakht\Parrot;

use Illuminate\Support\Facades\Redis;
use Aqayepardakht\Parrot\Traits\ClientToken;
use Aqayepardakht\Http\Request;
use Aqayepardakht\Parrot\Events\ParrotUnauthorized;
use Aqayepardakht\Parrot\Events\ParrotFaildRequest;

abstract class Parrot extends Request {
    use ClientToken;

    private $appendUrl;

    public function dispatch() {
        $service = (new ServiceDiscovery(get_class($this)))->getService();
        $response = $this->withToken($this->getToken($service))
                        ->send(
                            $service->getUrl().$this->appendUrl,
                            $service->getMethod()
                        );


        if ($response->status() === 401) {
            ParrotUnauthorized::dispatch($service);
            $this->removeToken($service->getServiceName());
            $this->dispatch();
        }

        if ($response->isFailed()) {
            ParrotFaildRequest::dispatch($service, $response);
            $response = unserialize(Redis::get($service->getUrl().$this->appendUrl));

            if (!$response) {

            }
        }

        if ($service->expirdTime()) {
            Redis::set($service->getUrl().$this->appendUrl, serialize($response), 'EX', $service->expirdTime());
        } else {
            Redis::set($service->getUrl().$this->appendUrl, serialize($response));
        }

        return $this->parser($response->json());
    }

    public function appendToUrl($url) {
        $this->appendUrl = $url;
    }

    abstract protected function parser($res);
}
