<?php

namespace Engelsystem\Test\Unit\Controllers\Metrics;

use DMS\PHPUnitExtensions\ArraySubset\ArraySubsetAsserts;
use Engelsystem\Config\Config;
use Engelsystem\Controllers\Metrics\Controller;
use Engelsystem\Controllers\Metrics\MetricsEngine;
use Engelsystem\Controllers\Metrics\Stats;
use Engelsystem\Helpers\Version;
use Engelsystem\Http\Exceptions\HttpForbidden;
use Engelsystem\Http\Request;
use Engelsystem\Http\Response;
use Engelsystem\Test\Unit\TestCase;
use Illuminate\Support\Collection;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LogLevel;
use Symfony\Component\HttpFoundation\ServerBag;

class ControllerTest extends TestCase
{
    use ArraySubsetAsserts;

    /**
     * @covers \Engelsystem\Controllers\Metrics\Controller::__construct
     * @covers \Engelsystem\Controllers\Metrics\Controller::metrics
     * @covers \Engelsystem\Controllers\Metrics\Controller::formatStats
     */
    public function testMetrics()
    {
        /** @var Response|MockObject $response */
        /** @var Request|MockObject $request */
        /** @var MetricsEngine|MockObject $engine */
        /** @var Stats|MockObject $stats */
        /** @var Config $config */
        /** @var Version|MockObject $version */
        list($response, $request, $engine, $stats, $config, $version) = $this->getMocks();

        $request->server = new ServerBag();
        $request->server->set('REQUEST_TIME_FLOAT', 0.0123456789);

        $engine->expects($this->once())
            ->method('get')
            ->willReturnCallback(function ($path, $data) use ($response) {
                $this->assertEquals('/metrics', $path);
                $this->assertArrayHasKey('info', $data);
                $this->assertArrayHasKey('users', $data);
                $this->assertArrayHasKey('licenses', $data);
                $this->assertArrayHasKey('users_working', $data);
                $this->assertArrayHasKey('work_seconds', $data);
                $this->assertArrayHasKey('worklog_seconds', $data);
                $this->assertArrayHasKey('vouchers', $data);
                $this->assertArrayHasKey('tshirts_issued', $data);
                $this->assertArrayHasKey('tshirt_sizes', $data);
                $this->assertArrayHasKey('locales', $data);
                $this->assertArrayHasKey('themes', $data);
                $this->assertArrayHasKey('shifts', $data);
                $this->assertArrayHasKey('announcements', $data);
                $this->assertArrayHasKey('questions', $data);
                $this->assertArrayHasKey('messages', $data);
                $this->assertArrayHasKey('password_resets', $data);
                $this->assertArrayHasKey('registration_enabled', $data);
                $this->assertArrayHasKey('database', $data);
                $this->assertArrayHasKey('sessions', $data);
                $this->assertArrayHasKey('oauth', $data);
                $this->assertArrayHasKey('log_entries', $data);
                $this->assertArrayHasKey('scrape_duration_seconds', $data);

                $this->assertArraySubset(['tshirt_sizes' => [
                    'type' => 'gauge',
                    ['labels' => ['size' => 'L'], 2],
                    ['labels' => ['size' => 'XL'], 0]
                ]], $data);

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

        $stats->expects($this->exactly(5))
            ->method('licenses')
            ->withConsecutive(['forklift'], ['car'], ['3.5t'], ['7.5t'], ['12.5t'])
            ->willReturnOnConsecutiveCalls(3, 15, 9, 7, 1);
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
        $stats->expects($this->exactly(2))
            ->method('announcements')
            ->withConsecutive([false], [true])
            ->willReturnOnConsecutiveCalls(18, 7);
        $stats->expects($this->exactly(2))
            ->method('questions')
            ->withConsecutive([true], [false])
            ->willReturnOnConsecutiveCalls(5, 0);
        $stats->expects($this->exactly(8))
            ->method('logEntries')
            ->withConsecutive(
                [LogLevel::EMERGENCY],
                [LogLevel::ALERT],
                [LogLevel::CRITICAL],
                [LogLevel::ERROR],
                [LogLevel::WARNING],
                [LogLevel::NOTICE],
                [LogLevel::INFO],
                [LogLevel::DEBUG]
            )
            ->willReturnOnConsecutiveCalls(0, 1, 0, 5, 999, 4, 55, 3);
        $this->setExpects($stats, 'newUsers', null, 9);
        $this->setExpects($stats, 'worklogSeconds', null, 39 * 60 * 60);
        $this->setExpects($stats, 'vouchers', null, 17);
        $this->setExpects($stats, 'tshirts', null, 3);
        $this->setExpects($stats, 'tshirtSizes', null, new Collection([
            ['shirt_size' => 'L', 'count' => 2],
        ]));
        $this->setExpects($stats, 'languages', null, new Collection([
            ['language' => 'en_US', 'count' => 5],
        ]));
        $this->setExpects($stats, 'themes', null, new Collection([
            ['theme' => '1', 'count' => 3],
        ]));
        $this->setExpects($stats, 'oauth', null, new Collection([
            ['provider' => 'test', 'count' => 2],
        ]));
        $this->setExpects($stats, 'shifts', null, 142);
        $this->setExpects($stats, 'messages', null, 3);
        $this->setExpects($stats, 'passwordResets', null, 1);
        $this->setExpects($stats, 'sessions', null, 1234);

        $config->set('registration_enabled', 1);
        $config->set('tshirt_sizes', [
            'L'  => 'Large',
            'XL' => 'X Large',
        ]);
        $config->set('locales', [
            'de_DE' => 'German',
            'en_US' => 'US English',
        ]);
        $config->set('themes', [
            0 => ['name' => 'Nothing'],
            1 => ['name' => 'Testing'],
        ]);
        $config->set('metrics', [
            'work'    => [60 * 60],
            'voucher' => [1],
        ]);
        $config->set('oauth', [
            'test' => ['name' => 'Test'],
        ]);

        $this->setExpects($version, 'getVersion', [], '0.42.42');

        $controller = new Controller($response, $engine, $config, $request, $stats, $version);
        $controller->metrics();
    }

    /**
     * @covers \Engelsystem\Controllers\Metrics\Controller::checkAuth
     * @covers \Engelsystem\Controllers\Metrics\Controller::stats
     */
    public function testStats()
    {
        /** @var Response|MockObject $response */
        /** @var Request|MockObject $request */
        /** @var MetricsEngine|MockObject $engine */
        /** @var Stats|MockObject $stats */
        /** @var Config $config */
        /** @var Version|MockObject $version */
        list($response, $request, $engine, $stats, $config, $version) = $this->getMocks();

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
                'users_in_action'    => 5,
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
            ->willReturn((int)(60 * 60 * 99.47));
        $this->setExpects($stats, 'newUsers', null, 3);
        $this->setExpects($stats, 'arrivedUsers', null, 10, $this->exactly(2));
        $this->setExpects($stats, 'currentlyWorkingUsers', null, 5);

        $controller = new Controller($response, $engine, $config, $request, $stats, $version);
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
        /** @var Version|MockObject $version */
        list($response, $request, $engine, $stats, $config, $version) = $this->getMocks();

        $request->expects($this->once())
            ->method('get')
            ->with('api_key')
            ->willReturn('LoremIpsum!');

        $config->set('api_key', 'fooBar!');

        $controller = new Controller($response, $engine, $config, $request, $stats, $version);

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
        $version = $this->createMock(Version::class);

        return [$response, $request, $engine, $stats, $config, $version];
    }
}
