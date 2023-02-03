<?php

declare(strict_types=1);

namespace Engelsystem\Http;

use Engelsystem\Config\Config;
use Engelsystem\Container\ServiceProvider;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

class RequestServiceProvider extends ServiceProvider
{
    protected array $appUrl;

    public function register(): void
    {
        /** @var Config $config */
        $config = $this->app->get('config');
        $trustedProxies = $config->get('trusted_proxies', []);
        $this->appUrl = parse_url($config->get('url') ?: '');

        if (!is_array($trustedProxies)) {
            $trustedProxies = empty($trustedProxies) ? [] : explode(',', preg_replace('~\s+~', '', $trustedProxies));
        }

        if (!empty($this->appUrl['path'])) {
            Request::setFactory([$this, 'createRequestWithoutPrefix']);
        }

        /** @var Request $request */
        $request = $this->app->call([Request::class, 'createFromGlobals']);
        $this->setTrustedProxies($request, $trustedProxies);

        $this->app->instance(Request::class, $request);
        $this->app->instance(SymfonyRequest::class, $request);
        $this->app->instance('request', $request);
    }

    /**
     * @param array $query GET parameters
     * @param array $request POST parameters
     * @param array $attributes Additional data
     * @param array $cookies Cookies
     * @param array $files Uploaded files
     * @param array $server Server env
     * @param mixed $content Request content
     */
    public function createRequestWithoutPrefix(
        array $query = [],
        array $request = [],
        array $attributes = [],
        array $cookies = [],
        array $files = [],
        array $server = [],
        mixed $content = null
    ): Request {
        if (
            !empty($this->appUrl['path'])
            && !empty($server['REQUEST_URI'])
            && Str::startsWith($server['REQUEST_URI'], $this->appUrl['path'])
        ) {
            $requestUri = Str::substr(
                $server['REQUEST_URI'],
                Str::length(rtrim($this->appUrl['path'], '/'))
            );

            // Reset paths which only contain the app path
            if ($requestUri && !Str::startsWith($requestUri, '/')) {
                $requestUri = $server['REQUEST_URI'];
            }

            $server['REQUEST_URI'] = $requestUri ?: '/';
        }

        return new Request($query, $request, $attributes, $cookies, $files, $server, $content);
    }

    /**
     * Set the trusted Proxies
     *
     * Required for unit tests (static methods can't be mocked)
     * @codeCoverageIgnore
     */
    protected function setTrustedProxies(
        Request $request,
        array $proxies,
        int $trustedHeadersSet = Request::HEADER_FORWARDED | Request::HEADER_X_FORWARDED_TRAEFIK
    ): void {
        $request->setTrustedProxies($proxies, $trustedHeadersSet);
    }
}
