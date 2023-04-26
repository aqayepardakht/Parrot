<?php

namespace Aqayepardakht\Parrot\Traits;

use Setting;
use Aqayepardakht\Parrot\Service;
use Aqayepardakht\Parrot\Events\ParrotCreateToken;
use Aqayepardakht\Parrot\Events\ParrotNullToken;
use Illuminate\Support\Facades\Redis;
use Aqayepardakht\Parrot\Events\ParrotUnauthorized;

trait ClientToken {
    protected function getToken(Service $service) {
        // if (!$service->hasAuth()) return null;

        if ($this->token) return $this->token;

        // if (Redis::get('token:'.$service->getServiceName())) 
        //     return Redis::get('token:'.$service->getServiceName());

        $params  = $service->getAuthParams();
        $baseUrl = $service->getBaseUrl();

        $result  = $this->send($baseUrl.'/oauth/token', 'POST', $service->getAuthParams());

        ParrotCreateToken::dispatch($service, $result);

        $result = $result->json();

        if (!empty($result->access_token)) {
            $this->setToken($service->getServiceName(), $result->access_token);
            return $result->access_token;
        }

        ParrotNullToken::dispatch($service);

        return null;
    }

    protected function handleUnauthorized(): Response
    {
        ParrotUnauthorized::dispatch($this->getService());

        $this->removeToken($this->getService()->getServiceName());

        return $this->delivery();
    }

    protected function setToken($service, $token) {
        // Redis::set('token:'.$service, $token);
    }

    protected function removeToken($service) {
        // Redis::del('token:'.$service);
    }
}
