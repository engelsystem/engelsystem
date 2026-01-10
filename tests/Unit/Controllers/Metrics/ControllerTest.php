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

        $matcher = $this->exactly(15);
        $stats->expects($matcher)
            ->method('licenses')->willReturnCallback(function (...$parameters) use ($matcher) {
                if ($matcher->numberOfInvocations() === 1) {
                    $this->assertSame('has_car', $parameters[0]);
                    return 6;
                }
                if ($matcher->numberOfInvocations() === 2) {
                    $this->assertSame('forklift', $parameters[0]);
                    return 3;
                }
                if ($matcher->numberOfInvocations() === 3) {
                    $this->assertSame('forklift', $parameters[0]);
                    return 15;
                }
                if ($matcher->numberOfInvocations() === 4) {
                    $this->assertSame('car', $parameters[0]);
                    return 9;
                }
                if ($matcher->numberOfInvocations() === 5) {
                    $this->assertSame('car', $parameters[0]);
                    $this->assertSame(true, $parameters[1]);
                    return 7;
                }
                if ($matcher->numberOfInvocations() === 6) {
                    $this->assertSame('3.5t', $parameters[0]);
                    return 1;
                }
                if ($matcher->numberOfInvocations() === 7) {
                    $this->assertSame('3.5t', $parameters[0]);
                    $this->assertSame(true, $parameters[1]);
                    return 5;
                }
                if ($matcher->numberOfInvocations() === 8) {
                    $this->assertSame('7.5t', $parameters[0]);
                    return 4;
                }
                if ($matcher->numberOfInvocations() === 9) {
                    $this->assertSame('7.5t', $parameters[0]);
                    $this->assertSame(true, $parameters[1]);
                    return 3;
                }
                if ($matcher->numberOfInvocations() === 10) {
                    $this->assertSame('12t', $parameters[0]);
                    return 5;
                }
                if ($matcher->numberOfInvocations() === 11) {
                    $this->assertSame('12t', $parameters[0]);
                    $this->assertSame(true, $parameters[1]);
                    return 9;
                }
                if ($matcher->numberOfInvocations() === 12) {
                    $this->assertSame('ifsg_light', $parameters[0]);
                    return 2;
                }
                if ($matcher->numberOfInvocations() === 13) {
                    $this->assertSame('ifsg_light', $parameters[0]);
                    $this->assertSame(true, $parameters[1]);
                    return 1;
                }
                if ($matcher->numberOfInvocations() === 14) {
                    $this->assertSame('ifsg', $parameters[0]);
                    return 7;
                }
                if ($matcher->numberOfInvocations() === 15) {
                    $this->assertSame('ifsg', $parameters[0]);
                    $this->assertSame(true, $parameters[1]);
                    return 8;
                }
            });

        $matcher = $this->exactly(4);
        $stats->expects($matcher)
            ->method('usersState')->willReturnCallback(function (...$parameters) use ($matcher) {
                if ($matcher->numberOfInvocations() === 1) {
                    $this->assertSame(false, $parameters[0]);
                    $this->assertSame(false, $parameters[1]);
                    return 7;
                }
                if ($matcher->numberOfInvocations() === 2) {
                    $this->assertSame(true, $parameters[0]);
                    $this->assertSame(false, $parameters[1]);
                    return 43;
                }
                if ($matcher->numberOfInvocations() === 3) {
                    $this->assertSame(false, $parameters[0]);
                    return 42;
                }
                if ($matcher->numberOfInvocations() === 4) {
                    $this->assertSame(true, $parameters[0]);
                    return 10;
                }
            });

        $matcher = $this->exactly(2);
        $stats->expects($matcher)
            ->method('currentlyWorkingUsers')->willReturnCallback(function (...$parameters) use ($matcher) {
                if ($matcher->numberOfInvocations() === 1) {
                    $this->assertSame(false, $parameters[0]);
                    return 10;
                }
                if ($matcher->numberOfInvocations() === 2) {
                    $this->assertSame(true, $parameters[0]);
                    return 1;
                }
            });

        $matcher = $this->exactly(3);
        $stats->expects($matcher)
            ->method('workSeconds')->willReturnCallback(function (...$parameters) use ($matcher) {
                if ($matcher->numberOfInvocations() === 1) {
                    $this->assertSame(true, $parameters[0]);
                    $this->assertSame(false, $parameters[1]);
                    return 60 * 37;
                }
                if ($matcher->numberOfInvocations() === 2) {
                    $this->assertSame(false, $parameters[0]);
                    $this->assertSame(false, $parameters[1]);
                    return 60 * 251;
                }
                if ($matcher->numberOfInvocations() === 3) {
                    $this->assertSame(null, $parameters[0]);
                    $this->assertSame(true, $parameters[1]);
                    return 60 * 3;
                }
            });

        $matcher = $this->exactly(2);
        $stats->expects($matcher)
            ->method('announcements')->willReturnCallback(function (...$parameters) use ($matcher) {
                if ($matcher->numberOfInvocations() === 1) {
                    $this->assertSame(false, $parameters[0]);
                    return 18;
                }
                if ($matcher->numberOfInvocations() === 2) {
                    $this->assertSame(true, $parameters[0]);
                    return 7;
                }
            });
        $matcher = $this->exactly(2);
        $stats->expects($matcher)
            ->method('questions')->willReturnCallback(function (...$parameters) use ($matcher) {
                if ($matcher->numberOfInvocations() === 1) {
                    $this->assertSame(true, $parameters[0]);
                    return 5;
                }
                if ($matcher->numberOfInvocations() === 2) {
                    $this->assertSame(false, $parameters[0]);
                    return 0;
                }
            });

        $matcher = $this->exactly(8);
        $stats->expects($matcher)
            ->method('logEntries')->willReturnCallback(function (...$parameters) use ($matcher) {
                if ($matcher->numberOfInvocations() === 1) {
                    $this->assertSame(LogLevel::EMERGENCY, $parameters[0]);
                    return 0;
                }
                if ($matcher->numberOfInvocations() === 2) {
                    $this->assertSame(LogLevel::ALERT, $parameters[0]);
                    return 1;
                }
                if ($matcher->numberOfInvocations() === 3) {
                    $this->assertSame(LogLevel::CRITICAL, $parameters[0]);
                    return 0;
                }
                if ($matcher->numberOfInvocations() === 4) {
                    $this->assertSame(LogLevel::ERROR, $parameters[0]);
                    return 5;
                }
                if ($matcher->numberOfInvocations() === 5) {
                    $this->assertSame(LogLevel::WARNING, $parameters[0]);
                    return 999;
                }
                if ($matcher->numberOfInvocations() === 6) {
                    $this->assertSame(LogLevel::NOTICE, $parameters[0]);
                    return 4;
                }
                if ($matcher->numberOfInvocations() === 7) {
                    $this->assertSame(LogLevel::INFO, $parameters[0]);
                    return 55;
                }
                if ($matcher->numberOfInvocations() === 8) {
                    $this->assertSame(LogLevel::DEBUG, $parameters[0]);
                    return 3;
                }
            });

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
