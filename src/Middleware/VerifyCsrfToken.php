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
    /** @var SessionInterface */
    protected $session;

    /**
     * @param SessionInterface $session
     */
    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    /**
     * Verify csrf tokens
     *
     * @param ServerRequestInterface  $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
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

    /**
     * @param ServerRequestInterface $request
     * @return bool
     */
    protected function isReading(ServerRequestInterface $request): bool
    {
        return in_array(
            $request->getMethod(),
            ['GET', 'HEAD', 'OPTIONS']
        );
    }

    /**
     * @param ServerRequestInterface $request
     * @return bool
     */
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
