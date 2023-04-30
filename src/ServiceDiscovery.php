<?php

namespace Aqayepardakht\Parrot;

class ServiceDiscovery {
    private $config;
    private $service;
    private $action;

    public function __construct($classname, ?array $config = null) {
        if ($adapter = strrpos($classname, '\\')) $adapter = substr($classname, $adapter + 1);

        $adapter = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $adapter));

        $this->service = strstr($adapter, '_', true);
        $this->action  = $adapter;
        $this->config = $this->prepareConfig($config);
    }

    protected function prepareConfig($config) {
        if ($config['url'] && $config['secret'] && $config['id']) {
            return [
                'services' => [
                    $this->service => [
                        'url' => $config['url'],
                        'actions' => [
                        $this->action => [
                            'auth' => [
                                'client_secret' => $config['secret'],
                                'client_id'     => $config['id'],
                                'grant_type'    => 'client_credentials'
                            ],
                        ]]
                    ]
                ]
            ];
        }

        return config('parrot');
    }

    public function existService(): bool {
        if (
            !array_key_exists('services', $this->config) ||
            !array_key_exists($this->service, $this->config['services']) ||
            !array_key_exists($this->action, $this->config['services'][$this->service]['actions'])
        ) return false;
                
        return true;
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
