<?php

namespace Engelsystem\Controllers\Metrics;

use Engelsystem\Config\Config;
use Engelsystem\Controllers\BaseController;
use Engelsystem\Http\Exceptions\HttpForbidden;
use Engelsystem\Http\Request;
use Engelsystem\Http\Response;

class Controller extends BaseController
{
    /** @var Config */
    protected $config;

    /** @var MetricsEngine */
    protected $engine;

    /** @var Request */
    protected $request;

    /** @var Response */
    protected $response;

    /** @var Stats */
    protected $stats;

    /**
     * @param Response      $response
     * @param MetricsEngine $engine
     * @param Config        $config
     * @param Request       $request
     * @param Stats         $stats
     */
    public function __construct(
        Response $response,
        MetricsEngine $engine,
        Config $config,
        Request $request,
        Stats $stats
    ) {
        $this->config = $config;
        $this->engine = $engine;
        $this->request = $request;
        $this->response = $response;
        $this->stats = $stats;
    }

    /**
     * @return Response
     */
    public function metrics()
    {
        $now = microtime(true);
        $this->checkAuth();

        $data = [
            $this->config->get('app_name') . ' stats',
            'users'                => [
                'type' => 'gauge',
                ['labels' => ['state' => 'incoming'], 'value' => $this->stats->newUsers()],
                ['labels' => ['state' => 'arrived', 'working' => 'no'], 'value' => $this->stats->arrivedUsers(false)],
                ['labels' => ['state' => 'arrived', 'working' => 'yes'], 'value' => $this->stats->arrivedUsers(true)],
            ],
            'users_working'        => [
                'type' => 'gauge',
                ['labels' => ['freeloader' => false], $this->stats->currentlyWorkingUsers(false)],
                ['labels' => ['freeloader' => true], $this->stats->currentlyWorkingUsers(true)],
            ],
            'work_seconds'         => [
                'type' => 'gauge',
                ['labels' => ['state' => 'done'], 'value' => $this->stats->workSeconds(true, false)],
                ['labels' => ['state' => 'planned'], 'value' => $this->stats->workSeconds(false, false)],
                ['labels' => ['state' => 'freeloaded'], 'value' => $this->stats->workSeconds(null, true)],
            ],
            'registration_enabled' => ['type' => 'gauge', $this->config->get('registration_enabled')],
        ];

        $data['scrape_duration_seconds'] = [
            'type' => 'gauge',
            'help' => 'Duration of the current request',
            microtime(true) - $this->request->server->get('REQUEST_TIME_FLOAT', $now)
        ];

        return $this->response
            ->withHeader('Content-Type', 'text/plain; version=0.0.4')
            ->withContent($this->engine->get('/metrics', $data));
    }

    /**
     * @return Response
     */
    public function stats()
    {
        $this->checkAuth(true);

        $data = [
            'user_count'         => $this->stats->newUsers() + $this->stats->arrivedUsers(),
            'arrived_user_count' => $this->stats->arrivedUsers(),
            'done_work_hours'    => round($this->stats->workSeconds(true) / 60 / 60, 0),
            'users_in_action'    => $this->stats->currentlyWorkingUsers(),
        ];

        return $this->response
            ->withHeader('Content-Type', 'application/json')
            ->withContent(json_encode($data));
    }

    /**
     * Ensure that the if the request is authorized
     *
     * @param bool $isJson
     */
    protected function checkAuth($isJson = false)
    {
        $apiKey = $this->config->get('api_key');
        if (empty($apiKey) || $this->request->get('api_key') == $apiKey) {
            return;
        }

        $message = 'The api_key is invalid';
        $headers = [];

        if ($isJson) {
            $message = json_encode(['error' => $message]);
            $headers['Content-Type'] = 'application/json';
        }

        throw new HttpForbidden($message, $headers);
    }
}
