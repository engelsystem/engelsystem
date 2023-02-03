<?php

declare(strict_types=1);

namespace Engelsystem\Middleware;

use Engelsystem\Application;
use InvalidArgumentException;
use LogicException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class Dispatcher implements MiddlewareInterface, RequestHandlerInterface
{
    use ResolvesMiddlewareTrait;

    protected ?RequestHandlerInterface $next = null;

    /**
     * @param MiddlewareInterface[]|string[] $stack
     */
    public function __construct(protected array $stack = [], protected ?Application $container = null)
    {
    }

    /**
     * Process an incoming server request and return a response, optionally delegating
     * response creation to a handler.
     *
     * Could be used to group middleware
     */
    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        $this->next = $handler;

        return $this->handle($request);
    }

    /**
     * Handle the request and return a response.
     *
     * It calls all configured middleware and handles their response
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $middleware = array_shift($this->stack);

        if (!$middleware) {
            if ($this->next) {
                return $this->next->handle($request);
            }

            throw new LogicException('Middleware queue is empty');
        }

        $middleware = $this->resolveMiddleware($middleware);
        if (!$middleware instanceof MiddlewareInterface) {
            throw new InvalidArgumentException('Middleware is no instance of ' . MiddlewareInterface::class);
        }

        return $middleware->process($request, $this);
    }

    public function setContainer(Application $container): void
    {
        $this->container = $container;
    }
}
