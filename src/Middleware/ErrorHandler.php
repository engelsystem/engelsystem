<?php

namespace Engelsystem\Middleware;

use Engelsystem\Http\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Twig_LoaderInterface as TwigLoader;

class ErrorHandler implements MiddlewareInterface
{
    /** @var TwigLoader */
    protected $loader;

    /** @var string */
    protected $viewPrefix = 'errors/';

    /**
     * @param TwigLoader $loader
     */
    public function __construct(TwigLoader $loader)
    {
        $this->loader = $loader;
    }

    /**
     * Handles any error messages
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
        $response = $handler->handle($request);

        $statusCode = $response->getStatusCode();
        if ($statusCode < 400 || !$response instanceof Response) {
            return $response;
        }

        $view = $this->selectView($statusCode);

        return $response->withView(
            $this->viewPrefix . $view,
            [
                'status'  => $statusCode,
                'content' => $response->getContent(),
            ],
            $statusCode,
            $response->getHeaders()
        );
    }

    /**
     * Select a view based on the given status code
     *
     * @param int $statusCode
     * @return string
     */
    protected function selectView(int $statusCode): string
    {
        $hundreds = intdiv($statusCode, 100);

        $viewsList = [$statusCode, $hundreds, $hundreds * 100];
        foreach ($viewsList as $view) {
            if ($this->loader->exists($this->viewPrefix . $view)) {
                return $view;
            }
        }

        return 'default';
    }
}
