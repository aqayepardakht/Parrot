<?php

namespace Aqayepardakht\Parrot\Traits;

use Setting;
use Aqayepardakht\Parrot\Service;
use Aqayepardakht\Parrot\Events\ParrotCreateToken;
use Aqayepardakht\Parrot\Events\ParrotNullToken;
use Illuminate\Support\Facades\Redis;

trait ClientToken {
    protected function getToken(Service $service) {
        if (Redis::get('token:'.$service->getServiceName())) return Redis::get('token:'.$service->getServiceName());

        $params  = $service->getAuthParams();
        $baseUrl = $service->getBaseUrl();
        $result  = $this->send($baseUrl.'/oauth/token', 'POST', $params);

        ParrotCreateToken::distpach($service, $result);

        $result = $result->json();

        if (!empty($result->access_token)) {
            $this->setToken($service->getServiceName(), $result->access_token);
            return $result->access_token;
        }

        ParrotNullToken::distpach($service);

        return null;
    }

    protected function setToken($service, $token) {
        Redis::set('token:'.$service, $token);
    }

    protected function removeToken($service) {
        Redis::del('token:'.$service);
    }
}
