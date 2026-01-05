<?php

declare(strict_types=1);

namespace Engelsystem\Controllers\OAuth2;

use Engelsystem\Controllers\BaseController;
use Engelsystem\Http\Request;
use Engelsystem\Http\Response;
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Exception\OAuthServerException;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;

class TokenController extends BaseController
{
    public function __construct(
        protected AuthorizationServer $server,
        protected LoggerInterface $log,
        protected Response $response
    ) {
    }

    /**
     * Handle token requests
     * POST /oauth2/token
     */
    public function token(Request $request): ResponseInterface
    {
        try {
            // Use a proper PSR-7 response that handles streams correctly
            $psrResponse = new \Nyholm\Psr7\Response();
            $psrResponse = $this->server->respondToAccessTokenRequest($request, $psrResponse);

            // Convert back to Engelsystem Response
            return $this->response
                ->withStatus($psrResponse->getStatusCode())
                ->withHeader('Content-Type', 'application/json')
                ->withContent((string) $psrResponse->getBody());
        } catch (OAuthServerException $e) {
            $this->log->warning('OAuth2 token error: {error}', [
                'error' => $e->getMessage(),
                'hint' => $e->getHint(),
            ]);

            $psrResponse = new \Nyholm\Psr7\Response();
            $psrResponse = $e->generateHttpResponse($psrResponse);

            return $this->response
                ->withStatus($psrResponse->getStatusCode())
                ->withHeader('Content-Type', 'application/json')
                ->withContent((string) $psrResponse->getBody());
        } catch (\Exception $e) {
            $this->log->error('OAuth2 token exception: {error}', [
                'error' => $e->getMessage(),
            ]);

            $oauthException = new OAuthServerException(
                'An unexpected error occurred',
                0,
                'server_error',
                500
            );

            $psrResponse = new \Nyholm\Psr7\Response();
            $psrResponse = $oauthException->generateHttpResponse($psrResponse);

            return $this->response
                ->withStatus($psrResponse->getStatusCode())
                ->withHeader('Content-Type', 'application/json')
                ->withContent((string) $psrResponse->getBody());
        }
    }
}
