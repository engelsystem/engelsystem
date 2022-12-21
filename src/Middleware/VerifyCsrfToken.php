<?php

namespace Engelsystem\Middleware;

use Engelsystem\Http\Exceptions\HttpAuthExpired;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class VerifyCsrfToken implements MiddlewareInterface
{
    public function __construct(protected SessionInterface $session)
    {
    }

    /**
     * Verify csrf tokens
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (
            $this->isReading($request)
            || $this->tokensMatch($request)
        ) {
            return $handler->handle($request);
        }

        throw new HttpAuthExpired('Authentication Token Mismatch');
    }

    protected function isReading(ServerRequestInterface $request): bool
    {
        return in_array(
            $request->getMethod(),
            ['GET', 'HEAD', 'OPTIONS']
        );
    }

    protected function tokensMatch(ServerRequestInterface $request): bool
    {
        $token = null;
        $body = $request->getParsedBody();
        $header = $request->getHeader('X-CSRF-TOKEN');

        if (is_array($body) && isset($body['_token'])) {
            $token = $body['_token'];
        }

        if (!empty($header)) {
            $header = array_shift($header);
        }

        $token = $token ?: $header;
        $sessionToken = $this->session->get('_token');

        return is_string($token)
            && is_string($sessionToken)
            && hash_equals($sessionToken, $token);
    }
}
