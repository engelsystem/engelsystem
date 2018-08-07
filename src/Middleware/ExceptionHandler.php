<?php

namespace Engelsystem\Middleware;

use Engelsystem\Exceptions\Handler as ExceptionsHandler;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ExceptionHandler implements MiddlewareInterface
{
    /** @var ContainerInterface */
    protected $container;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Handles any exceptions that occurred inside other middleware while returning it to the default response handler
     *
     * Should be added at the beginning
     *
     * @param ServerRequestInterface  $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        try {
            return $handler->handle($request);
        } catch (\Throwable $e) {
            /** @var ExceptionsHandler $handler */
            $handler = $this->container->get('error.handler');
            $content = $handler->exceptionHandler($e, true);

            return response($content, 500);
        }
    }
}
