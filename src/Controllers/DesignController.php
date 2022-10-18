<?php

namespace Engelsystem\Controllers;

use Engelsystem\Config\Config;
use Engelsystem\Helpers\BarChart;
use Engelsystem\Http\Response;
use Engelsystem\Models\User\PersonalData;
use Engelsystem\Models\User\State;
use Engelsystem\Models\User\User;

class DesignController extends BaseController
{
    /** @var Response */
    protected $response;

    /** @var Config */
    protected $config;

    /**
     * @param Response $response
     * @param Config   $config
     */
    public function __construct(Response $response, Config $config)
    {
        $this->config = $config;
        $this->response = $response;
    }

    /**
     * Show the design overview page
     *
     * @return Response
     */
    public function index()
    {
        $demoUser = (new User())->forceFill([
            'id'   => 42,
            'name' => 'test',
        ]);
        $demoUser->__set('state', (new State())->forceFill([
            'user_id' => 42,
            'arrived' => true,
        ]));
        $demoUser2 = (new User())->forceFill([
            'id'   => 1337,
            'name' => 'test2',
        ]);
        $demoUser2->__set('state', (new State())->forceFill([
            'user_id' => 1337,
            'arrived' => false,
        ]));
        $demoUser2->__set('personalData', (new PersonalData())->forceFill([
            'pronoun' => 'it/its',
        ]));

        $themes = $this->config->get('themes');
        $data = [
            'demo_user'    => $demoUser,
            'demo_user_2'  => $demoUser2,
            'themes'       => $themes,
            'bar_chart'    => BarChart::render(...BarChart::generateChartDemoData(23)),
            'timestamp30m' => time() + 30 * 60,
            'timestamp59m' => time() + 59 * 60,
            'timestamp1h'  => time() + 1 * 60 * 60,
            'timestamp1h30m'  => time() + 90 * 60,
            'timestamp1h31m'  => time() + 91 * 60,
            'timestamp2h'  => time() + 2 * 60 * 60,
            'timestamp2d'  => time() + 2 * 24 * 60 * 60,
            'timestamp3m' => time() + 3 * 30 * 24 * 60 * 60,
            'timestamp22y' => time() + 22 * 365 * 24 * 60 * 60,
            'timestamp30s' => time() + 30,

            'timestamp30mago' => time() - 30 * 60,
            'timestamp45mago' => time() - 45 * 60,
        ];

        return $this->response->withView(
            'pages/design',
            $data
        );
    }
}
