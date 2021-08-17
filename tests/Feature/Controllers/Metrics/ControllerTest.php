<?php

namespace Engelsystem\Test\Feature\Controllers\Metrics;

use Engelsystem\Controllers\Metrics\Controller;
use Engelsystem\Test\Feature\ApplicationFeatureTest;

class ControllerTest extends ApplicationFeatureTest
{
    /**
     * @covers \Engelsystem\Controllers\Metrics\Controller::metrics
     */
    public function testMetrics()
    {
        config([
            'api_key' => null,
            'metrics' => ['work' => [60 * 60], 'voucher' => [1]],
            'themes' => [1 => ['name' => 'Test']],
        ]);

        /** @var Controller $controller */
        $controller = app()->make(Controller::class);
        $response = $controller->metrics();

        $this->assertEquals(200, $response->getStatusCode());
    }
}
