<?php

declare(strict_types=1);

namespace Engelsystem\Events\Listener;

use Engelsystem\Config\Config;
use Engelsystem\Helpers\Authenticator;
use Engelsystem\Models\AngelType;
use Engelsystem\Models\User\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Psr\Log\LoggerInterface;

class OAuth2
{
    protected array $config;

    public function __construct(Config $config, protected LoggerInterface $log, protected Authenticator $auth)
    {
        $this->config = $config->get('oauth');
    }

    /**
     * @param string     $provider OAuth provider name
     * @param Collection $data OAuth userdata
     */
    public function login(string $event, string $provider, Collection $data): void
    {
        $user = $this->auth->user();
        $ssoTeams = $this->getSsoTeams($provider);
        $groupsKey = ($this->config[$provider] ?? [])['groups'] ?? 'groups';
        $userGroups = $data->get($groupsKey, []);

        foreach ($userGroups as $groupName) {
            if (!isset($ssoTeams[$groupName])) {
                continue;
            }

            $this->syncTeams($provider, $user, $ssoTeams[$groupName]);
        }
    }

    public function getSsoTeams(string $provider): array
    {
        $config = $this->config[$provider] ?? [];

        $teams = [];
        foreach ($config['teams'] ?? [] as $ssoName => $conf) {
            $conf = Arr::wrap($conf);
            $teamId = $conf['id'] ?? $conf[0];
            $isSupporter = $conf['supporter'] ?? false;

            $teams[$ssoName] = ['id' => $teamId, 'supporter' => $isSupporter];
        }

        return $teams;
    }

    protected function syncTeams(string $providerName, User $user, array $ssoTeam): void
    {
        $currentUserAngeltypes = $user->userAngelTypes;
        $angelType = AngelType::find($ssoTeam['id']);
        /** @var AngelType $userAngeltype */
        $userAngeltype = $currentUserAngeltypes->where('pivot.angel_type_id', $ssoTeam['id'])->first();
        $supporter = $ssoTeam['supporter'];
        $confirmed = $supporter ? $user->id : null;

        if (!$userAngeltype) {
            $this->log->info(
                'SSO {provider}: Added to angel type {angeltype}, confirmed: {confirmed}, supporter: {supporter}',
                [
                    'provider'  => $providerName,
                    'angeltype' => $angelType->name,
                    'confirmed' => $confirmed ? 'yes' : 'no',
                    'supporter' => $supporter ? 'yes' : 'no',
                ]
            );

            $user->userAngelTypes()->attach($angelType, ['supporter' => $supporter, 'confirm_user_id' => $confirmed]);

            return;
        }

        if (!$supporter) {
            return;
        }

        if ($userAngeltype->pivot->supporter != $supporter) {
            $userAngeltype->pivot->supporter = $supporter;
            $userAngeltype->pivot->save();

            $this->log->info(
                'SSO {provider}: Set supporter state for angeltype {angeltype}',
                [
                    'provider'  => $providerName,
                    'angeltype' => $userAngeltype->pivot->angelType->name,
                ]
            );
        }

        if (!$userAngeltype->pivot->confirm_user_id) {
            $userAngeltype->pivot->confirmUser()->associate($user);
            $userAngeltype->pivot->save();
            $this->log->info(
                'SSO {provider}: Set confirmed state for angeltype {angeltype}',
                [
                    'provider'  => $providerName,
                    'angeltype' => $userAngeltype->pivot->angelType->name,
                ]
            );
        }
    }
}
