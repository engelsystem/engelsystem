<?php

declare(strict_types=1);

namespace Engelsystem\Controllers\Admin;

use Engelsystem\Controllers\BaseController;
use Engelsystem\Controllers\HasUserNotifications;
use Engelsystem\Http\Redirector;
use Engelsystem\Http\Request;
use Engelsystem\Http\Response;
use Engelsystem\Models\AngelType;
use Engelsystem\Models\OAuth2Client;
use Engelsystem\OAuth2Server\Repository\ScopeRepository;
use Illuminate\Support\Str;
use Psr\Log\LoggerInterface;

class OAuth2ClientsController extends BaseController
{
    use HasUserNotifications;

    /** @var array<string, string> */
    protected array $permissions = [
        'oauth2.clients.edit',
    ];

    public function __construct(
        protected LoggerInterface $log,
        protected OAuth2Client $client,
        protected AngelType $angelType,
        protected Redirector $redirect,
        protected Response $response,
        protected ScopeRepository $scopeRepository
    ) {
    }

    public function index(): Response
    {
        $clients = $this->client->with('angelTypes')->orderBy('name')->get();

        return $this->response->withView('admin/oauth2-clients/index.twig', [
            'clients' => $clients,
        ]);
    }

    public function edit(Request $request): Response
    {
        $clientId = (int) $request->getAttribute('client_id');
        $client = $clientId ? $this->client->with('angelTypes')->find($clientId) : null;
        $angelTypes = $this->angelType->orderBy('name')->get();

        return $this->response->withView('admin/oauth2-clients/edit.twig', [
            'client' => $client,
            'angel_types' => $angelTypes,
            'available_scopes' => $this->scopeRepository->getAvailableScopes(),
            'available_grants' => $this->getAvailableGrants(),
        ]);
    }

    public function save(Request $request): Response
    {
        $clientId = (int) $request->getAttribute('client_id');

        /** @var OAuth2Client $client */
        $client = $clientId ? $this->client->findOrNew($clientId) : new OAuth2Client();

        if ($request->request->has('delete')) {
            return $this->delete($client);
        }

        $data = $this->validate($request, [
            'name' => 'required|max:255',
            'redirect_uris' => 'required',
            'grants' => 'required',
            'scopes' => 'optional',
            'confidential' => 'optional|checked',
            'active' => 'optional|checked',
            'angel_types' => 'optional',
            'regenerate_secret' => 'optional|checked',
        ]);

        $client->name = $data['name'];
        $client->redirect_uris = array_values(array_filter(
            array_map('trim', explode("\n", $data['redirect_uris']))
        ));
        $client->grants = is_array($data['grants'])
            ? implode(',', $data['grants'])
            : $data['grants'];
        $client->scopes = !empty($data['scopes']) ? (array) $data['scopes'] : null;
        $client->confidential = (bool) ($data['confidential'] ?? false);
        $client->active = (bool) ($data['active'] ?? false);

        // Generate identifier for new clients
        if (!$client->identifier) {
            $client->identifier = Str::random(40);
        }

        // Generate or regenerate secret
        $plainSecret = null;
        if ($client->confidential && (!$client->secret || !empty($data['regenerate_secret']))) {
            $plainSecret = Str::random(64);
            $client->secret = password_hash($plainSecret, PASSWORD_DEFAULT);
        }

        // Clear secret for public clients
        if (!$client->confidential) {
            $client->secret = null;
        }

        $client->save();

        // Sync angel types
        $angelTypeIds = !empty($data['angel_types']) ? (array) $data['angel_types'] : [];
        $client->angelTypes()->sync($angelTypeIds);

        $this->log->info(
            'Saved OAuth2 client "{name}" ({id})',
            ['name' => $client->name, 'id' => $client->id]
        );

        $this->addNotification('oauth2.client.saved');

        // Show the new secret if generated
        if ($plainSecret) {
            return $this->response->withView('admin/oauth2-clients/edit.twig', [
                'client' => $client->fresh(['angelTypes']),
                'angel_types' => $this->angelType->orderBy('name')->get(),
                'available_scopes' => $this->scopeRepository->getAvailableScopes(),
                'available_grants' => $this->getAvailableGrants(),
                'new_secret' => $plainSecret,
            ]);
        }

        return $this->redirect->to('/admin/oauth2-clients');
    }

    protected function delete(OAuth2Client $client): Response
    {
        if (!$client->id) {
            return $this->redirect->to('/admin/oauth2-clients');
        }

        $name = $client->name;
        $id = $client->id;

        // Delete related tokens first
        $client->accessTokens()->delete();
        $client->angelTypes()->detach();
        $client->delete();

        $this->log->info('Deleted OAuth2 client "{name}" ({id})', [
            'name' => $name,
            'id' => $id,
        ]);

        $this->addNotification('oauth2.client.deleted');

        return $this->redirect->to('/admin/oauth2-clients');
    }

    /**
     * @return array<string, string>
     */
    protected function getAvailableGrants(): array
    {
        return [
            'authorization_code' => 'Authorization Code',
            'refresh_token' => 'Refresh Token',
        ];
    }
}
