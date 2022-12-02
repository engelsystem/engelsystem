<?php

namespace Engelsystem\Events\Listener;

use Engelsystem\Config\Config;
use Engelsystem\Models\AngelType;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Psr\Log\LoggerInterface;

class OAuth2
{
    /** @var array */
    protected array $config;

    /** @var LoggerInterface */
    protected LoggerInterface $log;

    /**
     * @param Config          $config
     * @param LoggerInterface $log
     */
    public function __construct(Config $config, LoggerInterface $log)
    {
        $this->config = $config->get('oauth');
        $this->log = $log;
    }

    /**
     * @param string     $event
     * @param string     $provider OAuth provider name
     * @param Collection $data OAuth userdata
     */
    public function login(string $event, string $provider, Collection $data): void
    {
        $ssoTeams = $this->getSsoTeams($provider);
        $user = auth()->user();
        $currentUserAngeltypes = $user->userAngelTypes;
        $userGroups = $data->get(($this->config[$provider] ?? [])['groups'] ?? 'groups', []);

        foreach ($userGroups as $groupName) {
            if (!isset($ssoTeams[$groupName])) {
                continue;
            }

            $team = $ssoTeams[$groupName];
            $angelType = AngelType::find($team['id']);
            /** @var AngelType $userAngeltype */
            $userAngeltype = $currentUserAngeltypes->where('pivot.angel_type_id', $team['id'])->first();
            $supporter = $team['supporter'];
            $confirmed = $supporter ? $user->id : null;

            if (!$userAngeltype) {
                $this->log->info(
                    'SSO {provider}: Added to angeltype {angeltype}, confirmed: {confirmed}, supporter: {supporter}',
                    [
                        'provider'  => $provider,
                        'angeltype' => $angelType->name,
                        'confirmed' => $confirmed ? 'yes' : 'no',
                        'supporter' => $supporter ? 'yes' : 'no',
                    ]
                );

                $user->userAngelTypes()->attach($angelType, ['supporter' => $supporter, 'confirm_user_id' => $confirmed]);

                continue;
            }

            if (!$supporter) {
                continue;
            }

            if ($userAngeltype->pivot->supporter != $supporter) {
                $userAngeltype->pivot->supporter = $supporter;
                $userAngeltype->pivot->save();

                $this->log->info(
                    'SSO {provider}: Set supporter state for angeltype {angeltype}',
                    [
                        'provider'  => $provider,
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
                        'provider'  => $provider,
                        'angeltype' => $userAngeltype->pivot->angelType->name,
                    ]
                );
            }
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
}
