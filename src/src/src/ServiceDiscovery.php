<?php

namespace Aqayepardakht\Parrot;

class ServiceDiscovery {
    private $config;
    private $service;
    private $action;

    public function __construct($classname, ?array $config = null) {
        $this->config = config('parrot');

        if ($adapter = strrpos($classname, '\\')) $adapter = substr($classname, $adapter + 1);

        $adapter = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $adapter));

        $this->service = strstr($adapter, '_', true);
        $this->action  = substr(strstr($adapter, '_'), 1);
    }

    public function existService(): bool {
        if (array_key_exists('services', $this->config)) {
            if (array_key_exists($this->service, $this->config['services'])) {
                if (array_key_exists($this->action, $this->config['services'][$this->service]['actions'])) {
                    return true;
                }
            }
        }

        return false;
    }

    public function getService() : ?Service {
        if (!$this->existService()) return null;

        return new Service(
            $this->config['services'][$this->service],
            $this->service,
            $this->action
        );
    }

    public function existHttpMethod(string $service, string $http_method) : bool {
        $service = $this->getService($service);

        if ($service) {
            if (in_array(strtoupper($http_method), $service->getHttpMethods())) {
                return true;
            }
        }

        return false;
    }
}
