<?php

namespace Aqayepardakht\Parrot;

class Service {

    private $config;

    private $service;

    private $action;

    private $url;

    private $http_method;

    private $timeout;

    private $auth;

    public function __construct(array $config, string $service, string $action) {
        $this->config      = $config;
        $this->service     = $service;
        $this->action      = $action;
        $this->url         = $config['url'].$config['actions'][$action]['url'].'/';
        $this->http_method = $config['actions'][$action]['method'];
        $this->auth        = !empty($config['auth']) ? $config['auth']: null;
        // $this->timeout     = $config['timeout'];
    }

    public function getServiceName(): string {
        return $this->service;
    }

    public function getActionName(): string {
        $this->action;
    }

    public function getUrl(): string {
        return $this->url;
    }

    public function getBaseUrl(): string {
        $url = parse_url($this->url);

        $url['port'] = !empty($url['port']) ? $url['port'] : '';

        return $url['scheme'] . '://' . $url['host'].':'.$url['port'];
    }

    public function getMethod(): string {
        return $this->http_method;
    }

    public function getTimeout(): int {
        return $this->timeout;
    }

    public function getAuthParams() {
        return $this->auth;
    }

    public function expirdTime() {
        if (empty($this->config['actions'][$this->action]['cache'])) return false;
        return $this->config['actions'][$this->action]['cache'];
    }
}
