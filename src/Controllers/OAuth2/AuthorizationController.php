<?php

declare(strict_types=1);

namespace Engelsystem\Controllers\OAuth2;

use Engelsystem\Controllers\BaseController;
use Engelsystem\Controllers\HasUserNotifications;
use Engelsystem\Helpers\Authenticator;
use Engelsystem\Http\Redirector;
use Engelsystem\Http\Request;
use Engelsystem\Http\Response;
use Engelsystem\Models\OAuth2Client;
use Engelsystem\OAuth2Server\Entity\UserEntity;
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Exception\OAuthServerException;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class AuthorizationController extends BaseController
{
    use HasUserNotifications;

    public function __construct(
        protected AuthorizationServer $server,
        protected Authenticator $auth,
        protected LoggerInterface $log,
        protected OAuth2Client $clientModel,
        protected Redirector $redirect,
        protected Response $response,
        protected SessionInterface $session
    ) {
    }

    /**
     * Display authorization consent screen
     * GET /oauth2/authorize
     */
    public function authorize(Request $request): ResponseInterface
    {
        $user = $this->auth->user();

        if (!$user) {
            // Store OAuth request in session and redirect to login
            $this->session->set('oauth2_auth_request_params', $request->getQueryParams());
            return $this->redirect->to('/login');
        }

        try {
            $authRequest = $this->server->validateAuthorizationRequest($request);

            // Get client model for angeltype check
            $client = $this->clientModel
                ->with('angelTypes')
                ->where('identifier', $authRequest->getClient()->getIdentifier())
                ->first();

            if (!$client) {
                throw OAuthServerException::invalidClient($request);
            }

            // Check angeltype restrictions
            if (!$this->userCanAccessClient($user, $client)) {
                $this->log->warning('OAuth2 access denied: user {user} not in required angeltype for client {client}', [
                    'user' => $user->name,
                    'client' => $client->name,
                ]);

                throw OAuthServerException::accessDenied(
                    'You do not have permission to access this application. ' .
                    'You must be a confirmed member of one of the required angel types.'
                );
            }

            // Store auth request in session for POST
            $this->session->set('oauth2_auth_request', serialize($authRequest));

            return $this->response->withView('pages/oauth2/authorize.twig', [
                'client' => $client,
                'scopes' => $authRequest->getScopes(),
                'state' => $request->get('state'),
            ]);
        } catch (OAuthServerException $e) {
            $this->log->warning('OAuth2 authorization error: {error}', [
                'error' => $e->getMessage(),
                'hint' => $e->getHint(),
            ]);
            return $e->generateHttpResponse($this->response);
        }
    }

    /**
     * Process authorization decision
     * POST /oauth2/authorize
     */
    public function processAuthorization(Request $request): ResponseInterface
    {
        $user = $this->auth->user();

        if (!$user) {
            return $this->redirect->to('/login');
        }

        $serialized = $this->session->get('oauth2_auth_request');
        if (!$serialized) {
            $this->addNotification('oauth2.error.session_expired');
            return $this->redirect->to('/');
        }

        try {
            $authRequest = unserialize($serialized);
            $this->session->remove('oauth2_auth_request');

            if ($request->request->has('approve')) {
                $authRequest->setUser(new UserEntity($user));
                $authRequest->setAuthorizationApproved(true);

                $this->log->info('OAuth2 authorization approved for user {user} on client {client}', [
                    'user' => $user->name,
                    'client' => $authRequest->getClient()->getIdentifier(),
                ]);
            } else {
                $authRequest->setAuthorizationApproved(false);

                $this->log->info('OAuth2 authorization denied by user {user} for client {client}', [
                    'user' => $user->name,
                    'client' => $authRequest->getClient()->getIdentifier(),
                ]);
            }

            return $this->server->completeAuthorizationRequest($authRequest, $this->response);
        } catch (OAuthServerException $e) {
            $this->log->warning('OAuth2 authorization completion error: {error}', [
                'error' => $e->getMessage(),
            ]);
            return $e->generateHttpResponse($this->response);
        }
    }

    /**
     * Check if user can access this OAuth client based on angeltype restrictions
     */
    protected function userCanAccessClient($user, OAuth2Client $client): bool
    {
        $requiredAngelTypes = $client->angelTypes;

        // If no restrictions, allow all authenticated users
        if ($requiredAngelTypes->isEmpty()) {
            return true;
        }

        // Check if user is a confirmed member of any required angeltype
        $userAngelTypeIds = $user->userAngelTypes()
            ->whereNotNull('confirm_user_id')
            ->pluck('angel_types.id');

        return $requiredAngelTypes->pluck('id')
            ->intersect($userAngelTypeIds)
            ->isNotEmpty();
    }
}
