<?php

namespace Engelsystem\Middleware;

use Engelsystem\Http\Request;
use FastRoute\Dispatcher as FastRouteDispatcher;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class RouteDispatcher implements MiddlewareInterface
{
    /** @var FastRouteDispatcher */
    protected $dispatcher;

    /** @var ResponseInterface */
    protected $response;

    /** @var MiddlewareInterface|null */
    protected $notFound;

    /**
     * @param FastRouteDispatcher      $dispatcher
     * @param ResponseInterface        $response Default response
     * @param MiddlewareInterface|null $notFound Handles any requests if the route can't be found
     */
    public function __construct(
        FastRouteDispatcher $dispatcher,
        ResponseInterface $response,
        MiddlewareInterface $notFound = null
    ) {
        $this->dispatcher = $dispatcher;
        $this->response = $response;
        $this->notFound = $notFound;
    }

    /**
     * Process an incoming server request and return a response, optionally delegating
     * response creation to a handler.
     *
     * @param ServerRequestInterface  $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $path = $request->getUri()->getPath();
        if ($request instanceof Request) {
            $path = $request->getPathInfo();
        }

        $path = urldecode($path);
        $route = $this->dispatcher->dispatch($request->getMethod(), $path);

        $status = $route[0];
        if ($status == FastRouteDispatcher::NOT_FOUND) {
            if ($this->notFound instanceof MiddlewareInterface) {
                return $this->notFound->process($request, $handler);
            }

            return $this->response->withStatus(404);
        }

        if ($status == FastRouteDispatcher::METHOD_NOT_ALLOWED) {
            $methods = $route[1];
            return $this->response
                ->withStatus(405)
                ->withHeader('Allow', implode(', ', $methods));
        }

        $routeHandler = $route[1];
        $request = $request->withAttribute('route-request-handler', $routeHandler);
        $request = $request->withAttribute('route-request-path', $path);

        $vars = $route[2];
        foreach ($vars as $name => $value) {
            $request = $request->withAttribute($name, $value);
        }

        return $handler->handle($request);
    }
}
