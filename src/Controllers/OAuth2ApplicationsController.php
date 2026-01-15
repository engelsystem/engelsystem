<?php

declare(strict_types=1);

namespace Engelsystem\Controllers;

use Carbon\Carbon;
use Engelsystem\Helpers\Authenticator;
use Engelsystem\Http\Redirector;
use Engelsystem\Http\Request;
use Engelsystem\Http\Response;
use Engelsystem\Models\OAuth2AccessToken;
use Psr\Log\LoggerInterface;

class OAuth2ApplicationsController extends BaseController
{
    use HasUserNotifications;

    /** @var string[] */
    protected array $permissions = [
        'user_settings',
    ];

    public function __construct(
        protected Authenticator $auth,
        protected LoggerInterface $log,
        protected OAuth2AccessToken $accessTokenModel,
        protected Redirector $redirect,
        protected Response $response
    ) {
    }

    /**
     * List authorized OAuth2 applications for the current user
     * GET /settings/oauth2-applications
     */
    public function index(): Response
    {
        $user = $this->auth->user();

        // Get all active access tokens grouped by client
        $tokens = $this->accessTokenModel
            ->with('client')
            ->where('user_id', $user->id)
            ->where('revoked', false)
            ->where('expires_at', '>', Carbon::now())
            ->get()
            ->groupBy('oauth2_client_id');

        // Build application list with aggregated info
        $applications = [];
        foreach ($tokens as $clientTokens) {
            $firstToken = $clientTokens->first();
            if (!$firstToken->client) {
                continue;
            }

            $applications[] = [
                'client' => $firstToken->client,
                'scopes' => $this->aggregateScopes($clientTokens),
                'token_count' => $clientTokens->count(),
                'last_used' => $clientTokens->max('created_at'),
            ];
        }

        return $this->response->withView('pages/settings/oauth2-applications.twig', [
            'applications' => $applications,
        ]);
    }

    /**
     * Revoke all tokens for an application
     * POST /settings/oauth2-applications/revoke
     */
    public function revoke(Request $request): Response
    {
        $user = $this->auth->user();

        $data = $this->validate($request, [
            'client_id' => 'required|int',
        ]);

        $clientId = (int) $data['client_id'];

        // Revoke all access tokens for this client and user
        $tokens = $this->accessTokenModel
            ->where('user_id', $user->id)
            ->where('oauth2_client_id', $clientId)
            ->where('revoked', false)
            ->get();

        foreach ($tokens as $token) {
            $token->revoke();
        }

        $this->log->info('User {user} revoked OAuth2 access for client {client_id}', [
            'user' => $user->name,
            'client_id' => $clientId,
        ]);

        $this->addNotification('oauth2.application.revoked');

        return $this->redirect->to('/settings/oauth2-applications');
    }

    /**
     * Aggregate scopes from multiple tokens
     *
     * @return array<string>
     */
    protected function aggregateScopes(\Illuminate\Support\Collection $tokens): array
    {
        $scopes = [];
        foreach ($tokens as $token) {
            if (is_array($token->scopes)) {
                $scopes = array_merge($scopes, $token->scopes);
            }
        }
        return array_unique($scopes);
    }
}
