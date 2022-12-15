<?php

namespace Engelsystem\Test\Unit\Helpers;

use Engelsystem\Config\Config;
use Engelsystem\Helpers\DumpServerServiceProvider;
use Engelsystem\Test\Unit\ServiceProviderTest;
use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\CliDumper;
use Symfony\Component\VarDumper\Dumper\ServerDumper;

class DumpServerServiceProviderTest extends ServiceProviderTest
{
    /**
     * @covers \Engelsystem\Helpers\DumpServerServiceProvider::register
     */
    public function testRegisterIfClassExists(): void
    {
        if (class_exists(ServerDumper::class) === false) {
            self::markTestSkipped('ServerDumper class does not exist. Skipping.');
        }

        $varDumpServerConfig = [
            'host' => 'localhost',
            'port' => 80,
            'enable' => true
        ];

        $config = new Config();
        $config->set('var_dump_server', $varDumpServerConfig);
        $config->set('environment', 'development');

        // mock to test that the code has passed the enabled checks and started to configure the var dump server
        $app = $this->getApp(['get']);

        $app->expects(self::exactly(3))
            ->method('get')
            ->withConsecutive(
                ['config'],
                [CliDumper::class],
                [VarCloner::class]
            )->willReturnOnConsecutiveCalls(
                $config,
                new CliDumper(),
                new VarCloner()
            );

        $dumpServiceProvider = new DumpServerServiceProvider($app);
        $dumpServiceProvider->register();
    }

    public function notEnabledDataProvider(): array
    {
        return [
            [false, 'development'],
            [false, 'production'],
            [true, 'production'],
        ];
    }

    /**
     * @covers \Engelsystem\Helpers\DumpServerServiceProvider::register
     * @dataProvider notEnabledDataProvider
     */
    public function testRegisterShouldNotEnable(bool $enable, string $environment): void
    {
        $varDumpServerConfig = [
            'host' => 'localhost',
            'port' => 80,
            'enable' => $enable
        ];

        $config = new Config();
        $config->set('var_dump_server', $varDumpServerConfig);
        $config->set('environment', $environment);

        // asset get is called once only
        $app = $this->getApp(['get']);
        $app->expects(self::once())
            ->method('get')
            ->willReturn($config);

        $dumpServiceProvider = new DumpServerServiceProvider($app);
        $dumpServiceProvider->register();
    }
}
