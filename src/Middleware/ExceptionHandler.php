<?php

declare(strict_types=1);

namespace Engelsystem\Middleware;

use Engelsystem\Exceptions\Handler as ExceptionsHandler;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Throwable;

class ExceptionHandler implements MiddlewareInterface
{
    public function __construct(protected ContainerInterface $container)
    {
    }

    /**
     * Handles any exceptions that occurred inside other middleware while returning it to the default response handler
     *
     * Should be added at the beginning
     */
    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        try {
            return $handler->handle($request);
        } catch (Throwable $e) {
            /** @var ExceptionsHandler $handler */
            $handler = $this->container->get('error.handler');
            $content = $handler->exceptionHandler($e, false);

            return response($content, 500);
        }
    }
}
