<?php

namespace Engelsystem\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class SendResponseHandler implements MiddlewareInterface
{
    /**
     * Send the server response to the client
     *
     * This should be the first middleware
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

        if (!$this->headersSent()) {
            $this->sendHeader(sprintf(
                'HTTP/%s %s %s',
                $response->getProtocolVersion(),
                $response->getStatusCode(),
                $response->getReasonPhrase()
            ), true, $response->getStatusCode());

            foreach ($response->getHeaders() as $name => $values) {
                foreach ($values as $value) {
                    $this->sendHeader($name . ': ' . $value, false);
                }
            }
        }

        echo $response->getBody();
        return $response;
    }

    /**
     * Checks if headers have been sent
     *
     * @return bool
     * @codeCoverageIgnore
     */
    protected function headersSent()
    {
        return headers_sent();
    }

    /**
     * Send a raw HTTP header
     *
     * @param string $content
     * @param bool   $replace
     * @param int    $code
     * @codeCoverageIgnore
     */
    protected function sendHeader($content, $replace = true, $code = null)
    {
        header($content, $replace, $code);
    }
}
