<?php

declare(strict_types=1);

namespace Engelsystem\Middleware;

use Engelsystem\Container\Container;
use Engelsystem\Http\Response;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Wraps a callable to be used to respond / as a middleware
 */
class CallableHandler implements MiddlewareInterface, RequestHandlerInterface
{
    /** @var callable */
    protected $callable;

    /**
     * @param callable  $callable The callable that should be wrapped
     */
    public function __construct(callable $callable, protected ?Container $container = null)
    {
        $this->callable = $callable;
    }

    /**
     * Process an incoming server request and return a response, optionally delegating
     * response creation to a handler.
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        return $this->execute([$request, $handler]);
    }

    /**
     * Handle the request and return a response.
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return $this->execute([$request]);
    }

    /**
     * Execute the callable and return a response
     */
    protected function execute(array $arguments = []): ResponseInterface
    {
        $return = call_user_func_array($this->callable, $arguments);

        if ($return instanceof ResponseInterface) {
            return $return;
        }

        if (!$this->container instanceof Container) {
            throw new InvalidArgumentException('Unable to resolve response');
        }

        /** @var Response $response */
        $response = $this->container->get('response');
        return $response->withContent($return);
    }

    public function getCallable(): callable
    {
        return $this->callable;
    }
}
