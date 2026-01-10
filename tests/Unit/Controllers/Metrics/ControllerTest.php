<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Controllers\Metrics;

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
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LogLevel;
use Symfony\Component\HttpFoundation\ServerBag;

#[CoversMethod(Controller::class, '__construct')]
#[CoversMethod(Controller::class, 'metrics')]
#[CoversMethod(Controller::class, 'formatStats')]
#[CoversMethod(Controller::class, 'checkAuth')]
#[AllowMockObjectsWithoutExpectations]
class ControllerTest extends TestCase
{
    public function testMetrics(): void
    {
        /** @var Response&MockObject $response */
        /** @var Request&MockObject $request */
        /** @var MetricsEngine&MockObject $engine */
        /** @var Stats&MockObject $stats */
        /** @var Config $config */
        /** @var Version&MockObject $version */
        list($response, $request, $engine, $stats, $config, $version) = $this->getMocks();

        $request->server = new ServerBag();
        $request->server->set('REQUEST_TIME_FLOAT', 0.0123456789);

        $engine->expects($this->once())
            ->method('get')
            ->willReturnCallback(function ($path, $data) {
                $this->assertEquals('/metrics', $path);
                $this->assertArrayHasKey('info', $data);
                $this->assertArrayHasKey('users', $data);
                $this->assertArrayHasKey('licenses', $data);
                $this->assertArrayHasKey('users_working', $data);
                $this->assertArrayHasKey('work_seconds', $data);
                $this->assertArrayHasKey('worklog_seconds', $data);
                $this->assertArrayHasKey('vouchers', $data);
                $this->assertArrayHasKey('goodies_issued', $data);
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

                $this->assertArrayIsEqualToArrayOnlyConsideringListOfKeys([
                    'type' => 'gauge',
                    ['labels' => ['size' => 'L'], 2],
                    ['labels' => ['size' => 'XL'], 0],
                ], $data['tshirt_sizes'], ['type', 0, 1]);

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

        $stats->expects($this->exactly(15))
            ->method('licenses')
            ->willReturnMap([
               ['has_car', false, 6],
               ['forklift', false, 3],
               ['forklift', true, 15],
               ['car', false, 9],
               ['car', true, 7],
               ['3.5t', false, 1],
               ['3.5t', true, 5],
               ['7.5t', false, 4],
               ['7.5t', true, 3],
               ['12t', false, 5],
               ['12t', true, 9],
               ['ifsg_light', false, 2],
               ['ifsg_light', true, 1],
               ['ifsg', false, 7],
               ['ifsg', true, 8],
            ]);

        $stats->expects($this->exactly(4))
            ->method('usersState')
            ->willReturnMap([
                [false, false, 7],
                [true, false, 43],
                [false, true, 42],
                [true, true, 10],
            ]);

        $stats->expects($this->exactly(2))
            ->method('currentlyWorkingUsers')
            ->willReturnMap([
                [false, 10],
                [true, 1],
            ]);

        $matcher = $this->exactly(3);
        $stats->expects($matcher)
            ->method('workSeconds')
            ->willReturnMap([
                [true, false, 60 * 37],
                [false, false, 60 * 251],
                [null, true, 60 * 3],
            ]);

        $matcher = $this->exactly(2);
        $stats->expects($matcher)
            ->method('announcements')
            ->willReturnMap([
               [false, 18],
               [true, 7],
            ]);

        $matcher = $this->exactly(2);
        $stats->expects($matcher)
            ->method('questions')
            ->willReturnMap([
                [true, 5],
                [false, 0],
            ]);

        $matcher = $this->exactly(8);
        $stats->expects($matcher)
            ->method('logEntries')
            ->willReturnMap([
                [LogLevel::EMERGENCY, 0],
                [LogLevel::ALERT, 1],
                [LogLevel::CRITICAL, 0],
                [LogLevel::ERROR, 5],
                [LogLevel::WARNING, 999],
                [LogLevel::NOTICE, 4],
                [LogLevel::INFO, 55],
                [LogLevel::DEBUG, 3],
            ]);

        $this->setExpects($stats, 'worklogSeconds', null, 39 * 60 * 60);
        $this->setExpects($stats, 'vouchers', null, 17);
        $this->setExpects($stats, 'goodies', null, 3);
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
        $this->setExpects($stats, 'angelTypes', null, [[
            'name' => 'Test',
            'restricted' => true,
            'unconfirmed' => 3,
            'confirmed' => 2,
            'supporters' => 1,
        ]]);
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
            'de_DE',
            'en_US',
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

    public function testCheckAuth(): void
    {
        /** @var Response&MockObject $response */
        /** @var Request&MockObject $request */
        /** @var MetricsEngine&MockObject $engine */
        /** @var Stats&MockObject $stats */
        /** @var Config $config */
        /** @var Version&MockObject $version */
        list($response, $request, $engine, $stats, $config, $version) = $this->getMocks();

        $request->expects($this->once())
            ->method('get')
            ->with('api_key')
            ->willReturn('LoremIpsum!');

        $config->set('api_key', 'fooBar!');

        $controller = new Controller($response, $engine, $config, $request, $stats, $version);

        $this->expectException(HttpForbidden::class);
        $this->expectExceptionMessage('The api_key is invalid');
        $controller->metrics();
    }

    protected function getMocks(): array
    {
        $response = $this->createMock(Response::class);
        $request = $this->createMock(Request::class);
        $engine = $this->createMock(MetricsEngine::class);
        $stats = $this->createMock(Stats::class);
        $config = new Config();
        $version = $this->createMock(Version::class);

        return [$response, $request, $engine, $stats, $config, $version];
    }
}
