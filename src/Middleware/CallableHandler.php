<?php

namespace Engelsystem\Middleware;

use Engelsystem\Container\Container;
use Engelsystem\Http\Response;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class CallableHandler implements MiddlewareInterface, RequestHandlerInterface
{
    /** @var callable */
    protected $callable;

    /** @var Container */
    protected $container;

    /**
     * @param callable  $callable The callable that should be wrapped
     * @param Container $container
     */
    public function __construct(callable $callable, Container $container = null)
    {
        $this->callable = $callable;
        $this->container = $container;
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
        return $this->execute([$request, $handler]);
    }

    /**
     * Handle the request and return a response.
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return $this->execute([$request]);
    }

    /**
     * Execute the callable and return a response
     *
     * @param array $arguments
     * @return ResponseInterface
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

    /**
     * @return callable
     */
    public function getCallable()
    {
        return $this->callable;
    }
}
