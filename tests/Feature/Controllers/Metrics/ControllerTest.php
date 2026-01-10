<?php

declare(strict_types=1);

namespace Engelsystem\Test\Feature\Controllers\Metrics;

use Engelsystem\Controllers\Metrics\Controller;
use Engelsystem\Test\Feature\ApplicationFeatureTestCase;
use PHPUnit\Framework\Attributes\CoversMethod;

#[CoversMethod(Controller::class, 'metrics')]
class ControllerTest extends ApplicationFeatureTestCase
{
    public function testMetrics(): void
    {
        config([
            'api_key' => null,
            'metrics' => ['work' => [60 * 60], 'voucher' => [1]],
            'themes' => [1 => ['name' => 'Test']],
        ]);

        $controller = app()->make(Controller::class);
        $response = $controller->metrics();

        $this->assertEquals(200, $response->getStatusCode());
    }
}
