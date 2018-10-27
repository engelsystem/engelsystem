<?php

namespace Engelsystem\Middleware;

use Engelsystem\Config\Config;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class AddHeaders implements MiddlewareInterface
{
    /** @var Config */
    protected $config;

    /**
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * Process an incoming server request and setting the locale if required
     *
     * @param ServerRequestInterface  $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);
        if (!$this->config->get('add_headers', true)) {
            return $response;
        }

        $headers = $this->config->get('headers', []);

        foreach ($headers as $name => $value) {
            $response = $response->withAddedHeader($name, $value);
        }

        return $response;
    }
}
