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
    public function __construct(protected Response $response, protected Config $config)
    {
    }

    /**
     * Show the design overview page
     */
    public function index(): Response
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
        $date = new \DateTimeImmutable();
        $data = [
            'demo_user'       => $demoUser,
            'demo_user_2'     => $demoUser2,
            'themes'          => $themes,
            'bar_chart'       => BarChart::render(...BarChart::generateChartDemoData(23)),

            'timestamp30m'    => $date->add(new \DateInterval('PT30M')),
            'timestamp59m'    => $date->add(new \DateInterval('PT59M')),
            'timestamp1h'     => $date->add(new \DateInterval('PT1H')),
            'timestamp1h30m'  => $date->add(new \DateInterval('PT1H30M')),
            'timestamp1h31m'  => $date->add(new \DateInterval('PT1H31M')),
            'timestamp2h'     => $date->add(new \DateInterval('PT2H')),
            'timestamp2d'     => $date->add(new \DateInterval('P2D')),
            'timestamp3m'     => $date->add(new \DateInterval('P3M')),
            'timestamp22y'    => $date->add(new \DateInterval('P22Y')),
            'timestamp30s'    => $date->add(new \DateInterval('PT30S')),

            'timestamp30mago' => $date->sub(new \DateInterval('PT30M')),
            'timestamp45mago' => $date->sub(new \DateInterval('PT45M')),
        ];

        return $this->response->withView(
            'pages/design',
            $data
        );
    }
}
