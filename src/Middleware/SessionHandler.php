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
    /** @var SessionStorageInterface */
    protected $session;

    /** @var string[] */
    protected $paths = [];

    /**
     * @param SessionStorageInterface $session
     * @param array                   $paths
     */
    public function __construct(SessionStorageInterface $session, array $paths = [])
    {
        $this->paths = $paths;
        $this->session = $session;
    }

    /**
     * @param ServerRequestInterface  $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $return = $handler->handle($request);

        $cookies = $request->getCookieParams();
        if (
            $this->session instanceof NativeSessionStorage
            && in_array($request->getAttribute('route-request-path'), $this->paths)
            && !isset($cookies[$this->session->getName()])
        ) {
            $this->destroyNative();
        }

        return $return;
    }

    /**
     * @return bool
     * @codeCoverageIgnore
     */
    protected function destroyNative()
    {
        return session_destroy();
    }
}
