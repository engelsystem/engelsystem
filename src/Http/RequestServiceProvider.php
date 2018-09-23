<?php

namespace Engelsystem\Http;

use Engelsystem\Container\ServiceProvider;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

class RequestServiceProvider extends ServiceProvider
{
    public function register()
    {
        $config = $this->app->get('config');
        $trustedProxies = $config->get('trusted_proxies', []);

        if (!is_array($trustedProxies)) {
            $trustedProxies = empty($trustedProxies) ? [] : explode(',', preg_replace('~\s+~', '', $trustedProxies));
        }

        /** @var Request $request */
        $request = $this->app->call([Request::class, 'createFromGlobals']);
        $this->setTrustedProxies($request, $trustedProxies);

        $this->app->instance(Request::class, $request);
        $this->app->instance(SymfonyRequest::class, $request);
        $this->app->instance('request', $request);
    }

    /**
     * Set the trusted Proxies
     *
     * Required for unit tests (static methods can't be mocked)
     *
     * @param Request $request
     * @param array   $proxies
     * @param int     $trustedHeadersSet
     * @codeCoverageIgnore
     */
    protected function setTrustedProxies(
        $request,
        $proxies,
        $trustedHeadersSet = Request::HEADER_FORWARDED | Request::HEADER_X_FORWARDED_ALL
    ) {
        $request->setTrustedProxies($proxies, $trustedHeadersSet);
    }
}
