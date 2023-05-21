<?php

declare(strict_types=1);

namespace Engelsystem\Middleware;

use Illuminate\Support\Str;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;
use Symfony\Component\HttpFoundation\Session\Storage\SessionStorageInterface;

class SessionHandler implements MiddlewareInterface
{
    public function __construct(
        protected SessionStorageInterface $session,
        protected array $paths = [],
        protected ?string $apiPrefix = null
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $requestPath = $request->getAttribute('route-request-path');
        $isApi = in_array($requestPath, $this->paths)
            || ($this->apiPrefix && Str::startsWith($requestPath, $this->apiPrefix));
        $request = $request->withAttribute('route-api', $isApi);

        $return = $handler->handle($request);

        $cookies = $request->getCookieParams();
        if (
            $isApi
            && $this->session instanceof NativeSessionStorage
            && !isset($cookies[$this->session->getName()])
        ) {
            $this->destroyNative();
        }

        return $return;
    }

    /**
     * @codeCoverageIgnore
     */
    protected function destroyNative(): bool
    {
        return session_destroy();
    }
}
