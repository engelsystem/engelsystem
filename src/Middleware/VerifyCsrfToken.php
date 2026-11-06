<?php

declare(strict_types=1);

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
            || $this->isSessionlessApiRequest($request)
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

    /**
     * API clients authenticate via API key/bearer token, not a browser session - there is no
     * session-backed CSRF token to check, and no ambient browser credential for CSRF to protect.
     *
     * An API request carrying a logged-in session still needs a token: the Authenticator prefers
     * the session user over any API key, so a cross-site form could otherwise act as that user.
     */
    protected function isSessionlessApiRequest(ServerRequestInterface $request): bool
    {
        return $request->getAttribute('route-api') && !$this->session->get('user_id');
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
