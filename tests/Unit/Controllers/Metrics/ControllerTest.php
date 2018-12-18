<?php

namespace Engelsystem\Test\Unit\Controllers\Metrics;

use Engelsystem\Config\Config;
use Engelsystem\Controllers\Metrics\Controller;
use Engelsystem\Controllers\Metrics\MetricsEngine;
use Engelsystem\Controllers\Metrics\Stats;
use Engelsystem\Http\Exceptions\HttpForbidden;
use Engelsystem\Http\Request;
use Engelsystem\Http\Response;
use Engelsystem\Test\Unit\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\ServerBag;

class ControllerTest extends TestCase
{
    /**
     * @covers \Engelsystem\Controllers\Metrics\Controller::__construct
     * @covers \Engelsystem\Controllers\Metrics\Controller::metrics
     */
    public function testMetrics()
    {
        /** @var Response|MockObject $response */
        /** @var Request|MockObject $request */
        /** @var MetricsEngine|MockObject $engine */
        /** @var Stats|MockObject $stats */
        /** @var Config $config */
        list($response, $request, $engine, $stats, $config) = $this->getMocks();

        $request->server = new ServerBag();
        $request->server->set('REQUEST_TIME_FLOAT', 0.0123456789);

        $engine->expects($this->once())
            ->method('get')
            ->willReturnCallback(function ($path, $data) use ($response) {
                $this->assertEquals('/metrics', $path);
                $this->assertArrayHasKey('users', $data);
                $this->assertArrayHasKey('users_working', $data);
                $this->assertArrayHasKey('work_seconds', $data);
                $this->assertArrayHasKey('registration_enabled', $data);
                $this->assertArrayHasKey('scrape_duration_seconds', $data);

                return 'metrics return';
            });

        $response->expects($this->once())
            ->method('withHeader')
            ->with('Content-Type', 'text/plain; version=0.0.4')
            ->willReturn($response);
        $response->expects($this->once())
            ->method('withContent')
            ->with('metrics return')
            ->willReturn($response);

        $stats->expects($this->exactly(2))
            ->method('arrivedUsers')
            ->withConsecutive([false], [true])
            ->willReturnOnConsecutiveCalls(7, 43);
        $stats->expects($this->exactly(2))
            ->method('currentlyWorkingUsers')
            ->withConsecutive([false], [true])
            ->willReturnOnConsecutiveCalls(10, 1);
        $stats->expects($this->exactly(3))
            ->method('workSeconds')
            ->withConsecutive([true, false], [false, false], [null, true])
            ->willReturnOnConsecutiveCalls(60 * 37, 60 * 251, 60 * 3);
        $this->setExpects($stats, 'newUsers', null, 9);

        $config->set('registration_enabled', 1);

        $controller = new Controller($response, $engine, $config, $request, $stats);
        $controller->metrics();
    }

    /**
     * @covers \Engelsystem\Controllers\Metrics\Controller::stats
     * @covers \Engelsystem\Controllers\Metrics\Controller::checkAuth
     */
    public function testStats()
    {
        /** @var Response|MockObject $response */
        /** @var Request|MockObject $request */
        /** @var MetricsEngine|MockObject $engine */
        /** @var Stats|MockObject $stats */
        /** @var Config $config */
        list($response, $request, $engine, $stats, $config) = $this->getMocks();

        $response->expects($this->once())
            ->method('withHeader')
            ->with('Content-Type', 'application/json')
            ->willReturn($response);
        $response->expects($this->once())
            ->method('withContent')
            ->with(json_encode([
                'user_count'         => 13,
                'arrived_user_count' => 10,
                'done_work_hours'    => 99,
                'users_in_action'    => 5
            ]))
            ->willReturn($response);

        $request->expects($this->once())
            ->method('get')
            ->with('api_key')
            ->willReturn('ApiKey987');

        $config->set('api_key', 'ApiKey987');

        $stats->expects($this->once())
            ->method('workSeconds')
            ->with(true)
            ->willReturn(60 * 60 * 99.47);
        $this->setExpects($stats, 'newUsers', null, 3);
        $this->setExpects($stats, 'arrivedUsers', null, 10, $this->exactly(2));
        $this->setExpects($stats, 'currentlyWorkingUsers', null, 5);

        $controller = new Controller($response, $engine, $config, $request, $stats);
        $controller->stats();
    }

    /**
     * @covers \Engelsystem\Controllers\Metrics\Controller::checkAuth
     */
    public function testCheckAuth()
    {
        /** @var Response|MockObject $response */
        /** @var Request|MockObject $request */
        /** @var MetricsEngine|MockObject $engine */
        /** @var Stats|MockObject $stats */
        /** @var Config $config */
        list($response, $request, $engine, $stats, $config) = $this->getMocks();

        $request->expects($this->once())
            ->method('get')
            ->with('api_key')
            ->willReturn('LoremIpsum!');

        $config->set('api_key', 'fooBar!');

        $controller = new Controller($response, $engine, $config, $request, $stats);

        $this->expectException(HttpForbidden::class);
        $this->expectExceptionMessage(json_encode(['error' => 'The api_key is invalid']));
        $controller->stats();
    }

    /**
     * @return array
     */
    protected function getMocks(): array
    {
        /** @var Response|MockObject $response */
        $response = $this->createMock(Response::class);
        /** @var Request|MockObject $request */
        $request = $this->createMock(Request::class);
        /** @var MetricsEngine|MockObject $engine */
        $engine = $this->createMock(MetricsEngine::class);
        /** @var Stats|MockObject $stats */
        $stats = $this->createMock(Stats::class);
        $config = new Config();

        return array($response, $request, $engine, $stats, $config);
    }
}
