<?php

namespace Aqayepardakht\Parrot;

use Illuminate\Support\Facades\Redis;
use Aqayepardakht\Parrot\Traits\ClientToken;
use Aqayepardakht\Http\Request;
use Aqayepardakht\Http\Response;
use Aqayepardakht\Parrot\Events\ParrotUnauthorized;
use Aqayepardakht\Parrot\Events\ParrotFaildRequest;

abstract class Client extends Request
{
    use ClientToken;

    protected string $name;

    protected string $url;

    protected ?string $token = null;

    protected ?Service $service = null;

    private ?ServiceDiscovery $discoveryInstance = null;

    abstract protected function handleResponse(Response $response): void;

    public function dispatch(): Response
    {
        return $this->handle($this->delivery());
    }

    public function get(?array $params = null): Response
    {
        if ($params) {
            $this->appendParams($params);
        }

        return $this->delivery();
    }

    protected function prepareCredentials(): void
    {
        $this->withToken($this->getToken($this->getService()));
    }

    protected function prepareParams(): void
    {
        if (method_exists($this, 'params')) {
            $this->appendParams($this->params());
        }

        $this->appendParams([
            'service' => $this->service->getActionName()
        ]);
    }

    protected function delivery(): Response
    {
        $this->getService();

        $this->prepareCredentials();

        $this->prepareParams();

        $response = $this->send(
            $this->getService()->getUrl(), 
            'POST'
        );

        if ($response->status() === 401) {
            return $this->handleUnauthorized();
        }

        if ($response->isFailed()) {
            return $this->handleFailedRequest($response);
        }

        $this->cacheResponse($response);

        return $response;
    }

    protected function getService(): Service
    {
        $this->discover();

        if (!$this->service) {
            $this->service = $this->discoveryInstance->getService(); 
        }

        return $this->service;
    }

    protected function discover(): void
    {
        if (!$this->discoveryInstance) {
            $this->discoveryInstance = new ServiceDiscovery(get_class($this), get_object_vars($this));
        }
    }

    protected function handle(Response $response): Response
    {
        $this->handleResponse($response);

        return $response;
    }

    protected function handleFailedRequest(Response $response): Response
    {
        ParrotFailedRequest::dispatch($this->getService(), $response);

        $cachedResponse = Redis::get($this->getService()->getUrl() . $this->appendUrl);

        if ($cachedResponse) {
            return unserialize($cachedResponse);
        }

        throw new \Exception('Failed request');
    }

    protected function cacheResponse(Response $response): void
    {
        if ($this->getService()->expirdTime()) {
            Redis::set($this->getService()->getUrl() . $this->appendUrl, serialize($response), 'EX', $this->getService()->expirdTime());
        } else {
            Redis::set($this->getService()->getUrl() . $this->appendUrl, serialize($response));
        }
    }
}
