<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Config;

use Engelsystem\Application;
use Engelsystem\Config\Config;
use Engelsystem\Config\ConfigServiceProvider;
use Engelsystem\Helpers\Carbon;
use Engelsystem\Helpers\CarbonDay;
use Engelsystem\Models\EventConfig;
use Engelsystem\Test\Unit\HasDatabase;
use Engelsystem\Test\Unit\TestCase;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Support\Env;
use PHPUnit\Framework\Attributes\CoversMethod;

#[CoversMethod(ConfigServiceProvider::class, 'register')]
#[CoversMethod(ConfigServiceProvider::class, '__construct')]
#[CoversMethod(ConfigServiceProvider::class, 'loadConfigFromFiles')]
#[CoversMethod(ConfigServiceProvider::class, 'getConfigPath')]
#[CoversMethod(ConfigServiceProvider::class, 'initConfigOptions')]
#[CoversMethod(ConfigServiceProvider::class, 'loadConfigFromEnv')]
#[CoversMethod(ConfigServiceProvider::class, 'getEnvValue')]
#[CoversMethod(ConfigServiceProvider::class, 'boot')]
#[CoversMethod(ConfigServiceProvider::class, 'loadConfigFromDb')]
#[CoversMethod(ConfigServiceProvider::class, 'parseConfigTypes')]
class ConfigServiceProviderTest extends TestCase
{
    use HasDatabase;

    private array $configVarsWhereNullIsPruned =
        ['themes', 'tshirt_sizes', 'headers', 'header_items', 'footer_items', 'locales', 'contact_options'];

    public function testRegister(): void
    {
        $serviceProvider = new ConfigServiceProvider($this->app);
        $serviceProvider->register();

        $this->assertTrue($this->app->has('config'));
        $this->assertTrue($this->app->has(Config::class));
    }

    public function testRegisterRemovesNull(): void
    {
        $serviceProvider = new ConfigServiceProvider($this->app);
        $serviceProvider->register();

        /** @var Config $config */
        $config = $this->app->get('config');
        foreach ($this->configVarsWhereNullIsPruned as $name) {
            if (!$config->has($name)) {
                continue;
            }

            $this->assertNotEmpty($config->get($name));
        }

        $themes = $config->get('themes');
        // Persisted
        $this->assertArrayHasKey('foo', $themes);
        // Overwritten in local config
        $this->assertArrayNotHasKey('lorem', $themes);
    }

    public function testLoadConfigFromFilesIgnoreNotFound(): void
    {
        $this->app->instance('path.config', __DIR__ . '/Stub/unconfigured');

        $serviceProvider = new ConfigServiceProvider($this->app);
        $serviceProvider->register();

        /** @var Config $config */
        $config = $this->app->get('config');
        $this->assertArrayHasKey('unconfigured-config', $config->get(null));
    }

    public function testLoadConfigFromFileMerging(): void
    {
        $serviceProvider = new ConfigServiceProvider($this->app);
        $serviceProvider->register();

        /** @var Config $config */
        $config = $this->app->get('config');
        $conf = $config->get(null);
        $this->assertArrayNotHasKey('unconfigured-config', $conf);
        $this->assertArrayHasKey('app', $conf);
        $this->assertArrayHasKey('config-default', $conf);
        $this->assertArrayHasKey('config-local', $conf);
        $this->assertArrayHasKey('config', $conf);
        $this->assertArrayHasKey('file', $conf);

        $this->assertEquals('config.php', $conf['file']);
    }

    public function testInitConfigOptions(): void
    {
        $serviceProvider = new ConfigServiceProvider($this->app);
        $serviceProvider->register();

        /** @var Config $config */
        $config = $this->app->get('config');
        $conf = $config->get(null);

        $this->assertArrayHasKey('timezone', $conf);
        $this->assertEquals('Test/Testing', $conf['timezone']);

        $timezoneData = $conf['config_options']['system']['config']['timezone']['data'] ?? null;
        $this->assertNotEmpty($timezoneData);

        $firstKey = array_key_first($timezoneData);
        $this->assertEquals($firstKey, $timezoneData[$firstKey]);
    }

    public function testLoadConfigFromEnv(): void
    {
        $this->initDatabase();

        Env::getRepository()->set('VALUE_FROM_ENV', 'env value');
        Env::getRepository()->set('SOME_FOO', 'foo has a value');
        Env::getRepository()->set('MULTI_VAL', 'some, test,value');
        Env::getRepository()->set('ANOTHER_BAR_FILE', $this->app->get('path.config') . '/secret_file');

        $serviceProvider = new ConfigServiceProvider($this->app);
        $serviceProvider->register();

        /** @var Config $config */
        $config = $this->app->get('config');
        $conf = $config->get(null);
        $this->assertArrayNotHasKey('unconfigured-config', $conf);
        $this->assertArrayHasKey('from_env', $conf);

        $this->assertEquals('env value', $conf['from_env']);
        $this->assertEquals('foo has a value', $conf['some_foo']);
        $this->assertEquals(['some', 'test', 'value'], $conf['multi_val']);
        $this->assertEquals('something secret!' . PHP_EOL, $conf['another_bar']);

        $serviceProvider->boot();

        /** @var Config $config */
        $config = $this->app->get('config');
        $conf = $config->get(null);

        // Not existing value is set from default
        $this->assertArrayHasKey('not_set', $conf);
        $this->assertTrue($conf['not_set']);

        // Cleanup
        Env::getRepository()->clear('VALUE_FROM_ENV');
        Env::getRepository()->clear('SOME_FOO');
        Env::getRepository()->clear('ANOTHER_BAR_FILE');
    }

    public function testRegisterException(): void
    {
        $this->app->instance('path.config', __DIR__ . '/Stub/not_existing');

        $this->expectException(Exception::class);

        $serviceProvider = new ConfigServiceProvider($this->app);
        $serviceProvider->register();
    }

    public function testLoadConfigFromDb(): void
    {
        $this->initDatabase();
        (new EventConfig(['name' => 'in_database', 'value' => 'content']))->save();
        (new EventConfig(['name' => 'file', 'value' => 'database']))->save();
        (new EventConfig(['name' => 'themes', 'value' => ['foo' => 'test', 'bar' => 'baz']]))->save();

        $serviceProvider = new ConfigServiceProvider($this->app);
        $serviceProvider->register();
        $serviceProvider->boot();

        /** @var Config $config */
        $config = $this->app->get('config');

        $conf = $config->get(null);
        $this->assertArrayHasKey('in_database', $conf);
        $this->assertArrayHasKey('file', $conf);
        $this->assertArrayHasKey('themes', $conf);
        $this->assertArrayHasKey('env_config', $conf);
        $this->assertIsArray($conf['env_config']);

        $this->assertEquals('content', $conf['in_database']);
        $this->assertEquals('database', $conf['file']);
        $this->assertEquals(['foo' => 'test', 'bar' => 'baz'], $conf['themes']);
    }

    public function testLoadConfigFromDbIgnoreQueryError(): void
    {
        $config = $this->getMockBuilder(EventConfig::class)
            ->onlyMethods(['newQuery', '__call'])
            ->getMock();
        $this->setExpects($config, 'newQuery', null, $config, $this->atLeastOnce());
        $config->expects($this->once())
            ->method('__call')
            ->willReturnCallback(function (...$parameters): void {
                $this->assertEquals(['get', [['name', 'value']]], $parameters);
                throw new QueryException('', '', [], new Exception());
            });

        $serviceProvider = new ConfigServiceProvider($this->app, $config);
        $serviceProvider->register();
        $serviceProvider->boot();
    }

    public function testParseConfigTypes(): void
    {
        $serviceProvider = new ConfigServiceProvider($this->app);
        $serviceProvider->register();
        $serviceProvider->boot();

        /** @var Config $config */
        $config = $this->app->get('config');
        $conf = $config->get(null);

        $this->assertArrayHasKey('not_set', $conf);
        $this->assertTrue($conf['not_set']);

        $this->assertArrayHasKey('date_time', $conf);
        $this->assertInstanceOf(Carbon::class, $conf['date_time']);

        $this->assertArrayHasKey('date', $conf);
        $this->assertInstanceOf(CarbonDay::class, $conf['date']);

        $this->assertArrayHasKey('bool', $conf);
        $this->assertFalse($conf['bool']);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->app->instance('path.config', __DIR__ . '/Stub');
        Application::setInstance($this->app);
    }
}
