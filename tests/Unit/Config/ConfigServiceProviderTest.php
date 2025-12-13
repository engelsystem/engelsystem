<?php

declare(strict_types=1);

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

    private array $configVarsWhereNullIsPruned =
        ['themes', 'tshirt_sizes', 'headers', 'header_items', 'footer_items', 'locales', 'contact_options'];

    /**
     * @covers \Engelsystem\Config\ConfigServiceProvider::getConfigPath
     * @covers \Engelsystem\Config\ConfigServiceProvider::register
     */
    public function testRegister(): void
    {
        /** @var Application|MockObject $app */
        /** @var Config|MockObject $config */
        list($app, $config) = $this->getConfiguredApp(__DIR__ . '/../../../config');

        $config
            ->expects($this->exactly(4 + sizeof($this->configVarsWhereNullIsPruned)))
            ->method('get')
            ->with($this->callback(function (mixed $arg) {
                return is_null($arg) || in_array($arg, $this->configVarsWhereNullIsPruned);
            }))
            ->will($this->returnCallback(function (mixed $arg) {
                if (in_array($arg, $this->configVarsWhereNullIsPruned)) {
                    return [$arg . '_foo' => $arg . '_bar', $arg . '_willBePruned' => null];
                } elseif (is_null($arg)) {
                    return ['some' => 'value'];
                } else {
                    throw new Exception('Unexpected arg: ' . $arg);
                }
            }));

        $config
            ->expects($this->exactly(3 + sizeof($this->configVarsWhereNullIsPruned)))
            ->method('set')
            //With does not support a callback funtion with multiple args ...
            //Therefore, we misuse will
            ->will($this->returnCallback(function (mixed $key, mixed $value = null) {
                if (is_array($key)) {
                    return null;
                }
                if (in_array($key, $this->configVarsWhereNullIsPruned)) {
                    if ($value == [$key . '_foo' => $key . '_bar']) {
                        return null;
                    }
                    throw new Exception('Value for key ' . print_r($key, true) .
                                        'is not as expected: ' . print_r($value, true));
                }
                throw new Exception('Unexpected key: ' . print_r($key, true));
            }));

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
    public function testRegisterException(): void
    {
        /** @var Application|MockObject $app */
        /** @var Config|MockObject $config */
        list($app, $config) = $this->getConfiguredApp(__DIR__ . '/not_existing');

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
    public function testBoot(): void
    {
        $app = $this->getApp(['get', 'make']);

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
                    throw new QueryException('', '', [], new Exception());
                }

                return null;
            });

        $this->setExpects($eloquentBuilder, 'get', [['name', 'value']], $configs);
        $this->setExpects($app, 'get', ['config'], $config, $this->exactly(3));
        $this->setExpects($app, 'make', [EventConfig::class], $eventConfig, $this->exactly(1));

        $serviceProvider = new ConfigServiceProvider($app);
        $serviceProvider->boot();

        $serviceProvider = new ConfigServiceProvider($app, $eventConfig);
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

    /**
     * @return Application[]|Config[]
     */
    protected function getConfiguredApp(string $configPath): array
    {
        /** @var Config|MockObject $config */
        $config = $this->getMockBuilder(Config::class)
            ->getMock();

        $app = $this->getApp(['make', 'instance', 'get']);
        Application::setInstance($app);

        $this->setExpects($app, 'make', [Config::class], $config);
        $this->setExpects($app, 'get', ['path.config'], $configPath, $this->atLeastOnce());
        $app->expects($this->exactly(2))
            ->method('instance')
            ->withConsecutive(
                [Config::class, $config],
                ['config', $config]
            );

        return [$app, $config];
    }
}
