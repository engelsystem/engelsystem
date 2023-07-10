<?php

declare(strict_types=1);

namespace Engelsystem\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;
use Symfony\Component\HttpFoundation\Session\Storage\SessionStorageInterface;

class SessionHandler implements MiddlewareInterface
{
    public function __construct(protected SessionStorageInterface $session)
    {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $return = $handler->handle($request);

        $cookies = $request->getCookieParams();
        if (
            // Is api (accessible) path
            $request->getAttribute('route-api-accessible')
            // Uses native PHP session
            && $this->session instanceof NativeSessionStorage
            // No session cookie was sent on request
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
