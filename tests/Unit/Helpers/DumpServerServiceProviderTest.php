<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Helpers;

use Engelsystem\Config\Config;
use Engelsystem\Helpers\DumpServerServiceProvider;
use Engelsystem\Test\Unit\ServiceProviderTestCase;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\CliDumper;
use Symfony\Component\VarDumper\Dumper\ServerDumper;

#[CoversMethod(DumpServerServiceProvider::class, 'register')]
class DumpServerServiceProviderTest extends ServiceProviderTestCase
{
    public function testRegisterIfClassExists(): void
    {
        if (class_exists(ServerDumper::class) === false) {
            self::markTestSkipped('ServerDumper class does not exist. Skipping.');
        }

        $varDumpServerConfig = [
            'host' => 'localhost',
            'port' => 80,
            'enable' => true,
        ];

        $config = new Config();
        $config->set('var_dump_server', $varDumpServerConfig);
        $config->set('environment', 'development');

        // mock to test that the code has passed the enabled checks and started to configure the var dump server
        $app = $this->getAppMock(['get']);

        $matcher = self::exactly(3);
        $app->expects($matcher)
            ->method('get')->willReturnCallback(function (...$parameters) use ($config, $matcher) {
                if ($matcher->numberOfInvocations() === 1) {
                    $this->assertSame('config', $parameters[0]);
                    return $config;
                }
                if ($matcher->numberOfInvocations() === 2) {
                    $this->assertSame(CliDumper::class, $parameters[0]);
                    return new CliDumper();
                }
                if ($matcher->numberOfInvocations() === 3) {
                    $this->assertSame(VarCloner::class, $parameters[0]);
                    return new VarCloner();
                }
            });

        $dumpServiceProvider = new DumpServerServiceProvider($app);
        $dumpServiceProvider->register();
    }

    public static function notEnabledDataProvider(): array
    {
        return [
            [false, 'development'],
            [false, 'production'],
            [true, 'production'],
        ];
    }

    #[DataProvider('notEnabledDataProvider')]
    public function testRegisterShouldNotEnable(bool $enable, string $environment): void
    {
        $varDumpServerConfig = [
            'host' => 'localhost',
            'port' => 80,
            'enable' => $enable,
        ];

        $config = new Config();
        $config->set('var_dump_server', $varDumpServerConfig);
        $config->set('environment', $environment);

        // asset get is called once only
        $app = $this->getAppMock(['get']);
        $app->expects(self::once())
            ->method('get')
            ->willReturn($config);

        $dumpServiceProvider = new DumpServerServiceProvider($app);
        $dumpServiceProvider->register();
    }
}
