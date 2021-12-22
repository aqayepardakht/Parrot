<?php

namespace Aqayepardakht\Parrot\Traits;

use Setting;
use Aqayepardakht\Parrot\Service;
use Aqayepardakht\Parrot\Events\ParrotCreateToken;
use Aqayepardakht\Parrot\Events\ParrotNullToken;

trait ClientToken {
    protected function getToken(Service $service) {
        if (Setting::get($service->getServiceName(), false)) return Setting::get($service->getServiceName());

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
        Setting::set($service, $token);
        setting()->save();
    }

    protected function removeToken($service) {
        Setting::forget($service);
    }
}
