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

    protected bool $useCache = false;

    private ?ServiceDiscovery $discoveryInstance = null;

    protected function handleResponse(Response $response): void {}

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

        $cacheKey = $this->getService()->getUrl();

        if ($this->useCache && Redis::exists($cacheKey)) {
            return unserialize(Redis::get($cacheKey));
        }

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

        $this->cacheResponse($response, $cacheKey);

        return $response;
    }

    protected function handle(Response $response): Response
    {
        $this->handleResponse($response);

        return $response;
    }

    protected function handleUnauthorized(): Response
    {
        ParrotUnauthorized::dispatch($this->getService());

        $this->removeToken($this->getService()->getServiceName());

        return $this->delivery();
    }

    protected function handleFailedRequest(Response $response): Response
    {
        ParrotFailedRequest::dispatch($this->getService(), $response);

        return $this->delivery();
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

    protected function cacheResponse(Response $response, string $cacheKey): void
    {
        if (!$this->useCache) return;

        $expiration = $this->getService()->expirdTime();
        
        Redis::set($cacheKey, serialize($response), $expiration ? ['EX' => $expiration] : []);
    }
}
