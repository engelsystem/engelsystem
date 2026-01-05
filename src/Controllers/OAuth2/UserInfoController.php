<?php

declare(strict_types=1);

namespace Engelsystem\Controllers\OAuth2;

use Engelsystem\Controllers\BaseController;
use Engelsystem\Http\Request;
use Engelsystem\Http\Response;
use Engelsystem\Models\OAuth2AccessToken;
use Engelsystem\Models\User\User;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\ResourceServer;
use Psr\Log\LoggerInterface;

class UserInfoController extends BaseController
{
    public function __construct(
        protected ResourceServer $resourceServer,
        protected LoggerInterface $log,
        protected Response $response,
        protected OAuth2AccessToken $accessTokenModel,
        protected User $userModel
    ) {
    }

    /**
     * Return user information based on the access token
     * GET /oauth2/userinfo
     */
    public function userinfo(Request $request): Response
    {
        try {
            // Validate the access token
            $request = $this->resourceServer->validateAuthenticatedRequest($request);

            $tokenId = $request->getAttribute('oauth_access_token_id');
            $userId = $request->getAttribute('oauth_user_id');
            $scopes = $request->getAttribute('oauth_scopes', []);

            // Get the user
            $user = $this->userModel->find($userId);

            if (!$user) {
                return $this->errorResponse('User not found', 404);
            }

            $userInfo = $this->buildUserInfo($user, $scopes);

            return $this->response
                ->withHeader('Content-Type', 'application/json')
                ->withContent(json_encode($userInfo));
        } catch (OAuthServerException $e) {
            $this->log->warning('OAuth2 userinfo error: {error}', [
                'error' => $e->getMessage(),
            ]);

            return $this->response
                ->withStatus(401)
                ->withHeader('WWW-Authenticate', 'Bearer error="invalid_token"')
                ->withHeader('Content-Type', 'application/json')
                ->withContent(json_encode([
                    'error' => 'invalid_token',
                    'error_description' => $e->getMessage(),
                ]));
        } catch (\Exception $e) {
            $this->log->error('OAuth2 userinfo exception: {error}', [
                'error' => $e->getMessage(),
            ]);

            return $this->errorResponse('Internal server error', 500);
        }
    }

    /**
     * Build user info response based on granted scopes
     *
     * @param array<string> $scopes
     * @return array<string, mixed>
     */
    protected function buildUserInfo(User $user, array $scopes): array
    {
        // 'sub' is always included (required by OIDC)
        $info = [
            'sub' => (string) $user->id,
        ];

        if (in_array('profile', $scopes, true)) {
            $info['name'] = $user->displayName;
            $info['preferred_username'] = $user->name;

            if ($user->personalData && $user->personalData->pronoun) {
                $info['pronoun'] = $user->personalData->pronoun;
            }

            if ($user->personalData) {
                if ($user->personalData->first_name) {
                    $info['given_name'] = $user->personalData->first_name;
                }
                if ($user->personalData->last_name) {
                    $info['family_name'] = $user->personalData->last_name;
                }
            }
        }

        if (in_array('email', $scopes, true)) {
            $info['email'] = $user->email;
        }

        if (in_array('angeltypes', $scopes, true)) {
            $info['angeltypes'] = $user->userAngelTypes()
                ->whereNotNull('confirm_user_id')
                ->get()
                ->map(fn($angelType) => [
                    'id' => $angelType->id,
                    'name' => $angelType->name,
                    'supporter' => (bool) $angelType->pivot->supporter,
                ])
                ->toArray();
        }

        if (in_array('groups', $scopes, true)) {
            $info['groups'] = $user->groups->pluck('name')->toArray();
        }

        return $info;
    }

    protected function errorResponse(string $message, int $status): Response
    {
        return $this->response
            ->withStatus($status)
            ->withHeader('Content-Type', 'application/json')
            ->withContent(json_encode([
                'error' => 'server_error',
                'error_description' => $message,
            ]));
    }
}
