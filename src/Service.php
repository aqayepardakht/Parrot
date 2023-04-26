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
        // $this->url         = $config['url'].'parrot/'.$config['actions'][$action]['url'];
        $this->url         = $config['url'].'parrot/';
        $this->auth        = $config['actions'][$action]['auth'] ?? null;
        // $this->timeout     = $config['timeout'];
    }

    public function getServiceName(): string {
        return $this->service;
    }

    public function getActionName(): string {
        return $this->action;
    }

    public function getUrl(): string {
        return $this->url;
    }

    public function getBaseUrl(): string {
        $url = parse_url($this->url);
        $url['port'] = !empty($url['port']) ? $url['port'] : '';
        return $url['scheme'] . '://' . $url['host'];
    }

    public function getMethod(): string {
        return $this->http_method;
    }

    public function getTimeout(): int {
        return $this->timeout;
    }

    public function setAuthParams($client_id, $client_secret) {
        $this->auth['client_id']     = $client_id;
        $this->auth['client_secret'] = $client_secret;        

        return $this;
    }

    public function getAuthParams() {
        return $this->auth;
    }

    public function hasAuth() {
        return !!$this->auth;
    }

    public function expirdTime() {
        if (empty($this->config['actions'][$this->action]['cache'])) return false;
        return $this->config['actions'][$this->action]['cache'];
    }
}
