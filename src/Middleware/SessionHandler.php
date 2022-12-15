<?php

namespace Engelsystem\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;
use Symfony\Component\HttpFoundation\Session\Storage\SessionStorageInterface;

class SessionHandler implements MiddlewareInterface
{
    public function __construct(protected SessionStorageInterface $session, protected array $paths = [])
    {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $requestPath = $request->getAttribute('route-request-path');

        $return = $handler->handle($request);

        $cookies = $request->getCookieParams();
        if (
            $this->session instanceof NativeSessionStorage
            && in_array($requestPath, $this->paths)
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
