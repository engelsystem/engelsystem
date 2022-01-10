<?php

namespace Engelsystem\Events\Listener;

use Engelsystem\Config\Config;
use Engelsystem\Database\Database;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Psr\Log\LoggerInterface;

class OAuth2
{
    /** @var array */
    protected $config;

    /** @var LoggerInterface */
    protected $log;

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
     * @param string     $provider
     * @param Collection $data OAuth userdata
     */
    public function login(string $event, string $provider, Collection $data)
    {
        /** @var Database $db */
        $db = app(Database::class);
        $ssoTeams = $this->getSsoTeams($provider);
        $user = auth()->user();
        $currentUserAngeltypes = collect($db->select('SELECT * FROM UserAngelTypes WHERE user_id = ?', [$user->id]));
        $userGroups = $data->get(($this->config[$provider] ?? [])['groups'] ?? 'groups', []);

        foreach ($userGroups as $groupName) {
            if (!isset($ssoTeams[$groupName])) {
                continue;
            }

            $team = $ssoTeams[$groupName];
            $userAngeltype = $currentUserAngeltypes->where('angeltype_id', $team['id'])->first();
            $supporter = $team['supporter'];
            $confirmed = $supporter ? $user->id : null;

            if (!$userAngeltype) {
                $this->log->info(
                    'SSO {provider}: Added to angeltype {angeltype}, confirmed: {confirmed}, supporter: {supporter}',
                    [
                        'provider'  => $provider,
                        'angeltype' => AngelType($team['id'])['name'],
                        'confirmed'   => $confirmed ? 'yes' : 'no',
                        'supporter' => $supporter ? 'yes' : 'no',
                    ]
                );
                $db->insert(
                    'INSERT INTO UserAngelTypes (user_id, angeltype_id, confirm_user_id, supporter) VALUES (?, ?, ?, ?)',
                    [$user->id, $team['id'], $confirmed, $supporter]
                );

                continue;
            }

            if (!$supporter) {
                continue;
            }

            if ($userAngeltype->supporter != $supporter) {
                $db->update(
                    'UPDATE UserAngelTypes SET supporter=? WHERE id = ?',
                    [$supporter, $userAngeltype->id]
                );
                $this->log->info(
                    'SSO {provider}: Set supporter state for angeltype {angeltype}',
                    [
                        'provider'  => $provider,
                        'angeltype' => AngelType($userAngeltype->angeltype_id)['name'],
                    ]
                );
            }

            if (!$userAngeltype->confirm_user_id) {
                $db->update(
                    'UPDATE UserAngelTypes SET confirm_user_id=? WHERE id = ?',
                    [$user->id, $userAngeltype->id]
                );
                $this->log->info(
                    'SSO {provider}: Set confirmed state for angeltype {angeltype}',
                    [
                        'provider'  => $provider,
                        'angeltype' => AngelType($userAngeltype->angeltype_id)['name'],
                    ]
                );
            }
        }
    }

    /**
     * @param string $provider
     * @return array
     */
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
