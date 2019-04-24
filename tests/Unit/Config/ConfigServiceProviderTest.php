<?php

namespace Engelsystem\Test\Unit\Config;

use DMS\PHPUnitExtensions\ArraySubset\ArraySubsetAsserts;
use Engelsystem\Application;
use Engelsystem\Config\Config;
use Engelsystem\Config\ConfigServiceProvider;
use Engelsystem\Models\EventConfig;
use Engelsystem\Test\Unit\ServiceProviderTest;
use Exception;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\QueryException;
use PHPUnit\Framework\MockObject\MockObject;

class ConfigServiceProviderTest extends ServiceProviderTest
{
    use ArraySubsetAsserts;

    /**
     * @covers \Engelsystem\Config\ConfigServiceProvider::getConfigPath
     * @covers \Engelsystem\Config\ConfigServiceProvider::register
     */
    public function testRegister()
    {
        /** @var Config|MockObject $config */
        $config = $this->getMockBuilder(Config::class)
            ->getMock();

        $app = $this->getApp(['make', 'instance', 'get']);
        Application::setInstance($app);

        $this->setExpects($app, 'make', [Config::class], $config);
        $this->setExpects($app, 'get', ['path.config'], __DIR__ . '/../../../config', $this->atLeastOnce());
        $app->expects($this->exactly(2))
            ->method('instance')
            ->withConsecutive(
                [Config::class, $config],
                ['config', $config]
            );

        $this->setExpects($config, 'set', null, null, $this->exactly(2));
        $config->expects($this->exactly(3))
            ->method('get')
            ->with(null)
            ->willReturnOnConsecutiveCalls([], [], ['lor' => 'em']);

        $configFile = __DIR__ . '/../../../config/config.php';
        $configExists = file_exists($configFile);
        if (!$configExists) {
            file_put_contents($configFile, '<?php return ["lor"=>"em"];');
        }

        $serviceProvider = new ConfigServiceProvider($app);
        $serviceProvider->register();

        if (!$configExists) {
            unlink($configFile);
        }
    }

    /**
     * @covers \Engelsystem\Config\ConfigServiceProvider::register
     */
    public function testRegisterException()
    {
        /** @var Config|MockObject $config */
        $config = $this->getMockBuilder(Config::class)
            ->getMock();

        $app = $this->getApp(['make', 'instance', 'get']);
        Application::setInstance($app);

        $this->setExpects($app, 'make', [Config::class], $config);
        $app->expects($this->exactly(2))
            ->method('instance')
            ->withConsecutive(
                [Config::class, $config],
                ['config', $config]
            );
        $this->setExpects($app, 'get', ['path.config'], __DIR__ . '/not_existing', $this->atLeastOnce());

        $this->setExpects($config, 'set', null, null, $this->never());
        $this->setExpects($config, 'get', [null], []);

        $this->expectException(Exception::class);

        $serviceProvider = new ConfigServiceProvider($app);
        $serviceProvider->register();
    }

    /**
     * @covers \Engelsystem\Config\ConfigServiceProvider::__construct
     * @covers \Engelsystem\Config\ConfigServiceProvider::boot
     */
    public function testBoot()
    {
        $app = $this->getApp(['get']);

        /** @var EventConfig|MockObject $eventConfig */
        $eventConfig = $this->createMock(EventConfig::class);
        /** @var EloquentBuilder|MockObject $eloquentBuilder */
        $eloquentBuilder = $this->getMockBuilder(EloquentBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $config = new Config(['foo' => 'bar', 'lorem' => ['ipsum' => 'dolor', 'bla' => 'foo']]);

        $configs = [
            new EventConfig(['name' => 'test', 'value' => 'testing']),
            new EventConfig(['name' => 'lorem', 'value' => ['ipsum' => 'tester']]),
        ];

        $returnValue = $eloquentBuilder;
        $eventConfig
            ->expects($this->exactly(3))
            ->method('newQuery')
            ->willReturnCallback(function () use (&$returnValue) {
                if ($returnValue instanceof EloquentBuilder) {
                    $return = $returnValue;
                    $returnValue = null;
                    return $return;
                }

                if (is_null($returnValue)) {
                    throw new QueryException('', [], new Exception());
                }

                return null;
            });

        $this->setExpects($eloquentBuilder, 'get', [['name', 'value']], $configs);
        $this->setExpects($app, 'get', ['config'], $config, $this->exactly(3));

        $serviceProvider = new ConfigServiceProvider($app);
        $serviceProvider->boot();

        $serviceProvider = new ConfigServiceProvider($app, $eventConfig);
        $serviceProvider->boot();
        $serviceProvider->boot();
        $serviceProvider->boot();

        $this->assertArraySubset(
            [
                'foo'   => 'bar',
                'lorem' => [
                    'ipsum' => 'tester',
                    'bla'   => 'foo',
                ],
                'test'  => 'testing',
            ],
            $config->get(null)
        );
    }
}
