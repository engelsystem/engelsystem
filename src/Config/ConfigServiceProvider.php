<?php

declare(strict_types=1);

namespace Engelsystem\Config;

use DateTimeZone;
use Engelsystem\Application;
use Engelsystem\Container\ServiceProvider;
use Engelsystem\Helpers\Carbon;
use Engelsystem\Models\EventConfig;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Support\Env;
use Illuminate\Support\Str;

/**
 * Loads the configuration from files (for database connection and app config), database and environment
 */
class ConfigServiceProvider extends ServiceProvider
{
    protected array $configFiles = ['app.php', 'config.default.php', 'config.local.php', 'config.php'];

    // Remember to update ConfigServiceProviderTest, config.default.php, and README.md
    protected array $configVarsToPruneNulls = [
        'themes',
        'tshirt_sizes',
        'headers',
        'header_items',
        'footer_items',
        'locales',
        'contact_options',
    ];

    protected array $envConfig = [];

    public function __construct(Application $app, protected ?EventConfig $eventConfig = null)
    {
        parent::__construct($app);
    }

    public function register(): void
    {
        /** @var Config $config */
        $config = $this->app->make(Config::class);
        $this->app->instance(Config::class, $config);
        $this->app->instance('config', $config);

        $this->loadConfigFromFiles($config);
        $this->initConfigOptions($config);
        $this->loadConfigFromEnv($config);

        if (empty($config->get(null))) {
            throw new Exception('Configuration not found');
        }

        // Prune values with null in file config to remove them
        foreach ($this->configVarsToPruneNulls as $key) {
            $values = $config->get($key);
            if (!$values) {
                // Skip values that are not defined in files or env
                continue;
            }
            $config->set($key, array_filter($values, function ($v) {
                return !is_null($v);
            }));
        }
    }

    public function boot(): void
    {
        /** @var Config $config */
        $config = $this->app->get('config');

        $this->loadConfigFromDb($config);
        $this->loadConfigFromEnv($config);

        $this->parseConfigTypes($config);

        $config->set('env_config', $this->envConfig);
    }

    protected function loadConfigFromFiles(Config $config): void
    {
        foreach ($this->configFiles as $file) {
            $file = $this->getConfigPath($file);

            if (!file_exists($file)) {
                continue;
            }

            $configuration = array_replace_recursive(
                $config->get(null),
                require $file
            );
            $config->set($configuration);
        }
    }

    protected function initConfigOptions(Config $config): void
    {
        $configOptions = $config['config_options'];
        if ($configOptions['system']['config']['timezone'] ?? null) {
            // Timezone must be set for database connection
            $configOptions['system']['config']['timezone']['data'] = array_combine(
                DateTimeZone::listIdentifiers(),
                DateTimeZone::listIdentifiers()
            );
            $config['timezone'] = $config['timezone']
                ?? $configOptions['system']['config']['timezone']['default']
                ?? 'UTC';

            $config->set('config_options', $configOptions);
        }
    }

    protected function loadConfigFromEnv(Config $config): void
    {
        foreach ($config->get('config_options', []) as $options) {
            foreach ($options['config'] as $name => $option) {
                $value = $this->getEnvValue(empty($option['env']) ? $name : $option['env']);
                if (is_null($value)) {
                    continue;
                }

                $config->set($name, $value);
            }
        }
    }

    protected function getEnvValue(string $name): mixed
    {
        $name = Str::upper($name);
        if (isset($this->envConfig[$name])) {
            return $this->envConfig[$name];
        }

        $file = Env::get($name  . '_FILE');
        if (!is_null($file) && is_readable($file)) {
            $value = file_get_contents($file);
        } else {
            $value = Env::get($name);
        }

        $this->envConfig[$name] = $value;

        return $value;
    }

    protected function loadConfigFromDb(Config $config): void
    {
        if (!$this->eventConfig) {
            $this->eventConfig = $this->app->make(EventConfig::class);
        }

        try {
            /** @var EventConfig[] $values */
            $values = $this->eventConfig->newQuery()->get(['name', 'value']);
        } catch (QueryException) {
            return;
        }

        foreach ($values as $option) {
            $data = $option->value;

            if (is_array($data) && $config->has($option->name)) {
                $data = array_replace_recursive(
                    $config->get($option->name),
                    $data
                );
            }

            $config->set($option->name, $data);
        }
    }

    protected function parseConfigTypes(Config $config): void
    {
        // Parse config types
        foreach ($config->get('config_options', []) as $page) {
            foreach ($page['config'] as $name => $options) {
                $value = $config->get($name, $options['default'] ?? null);

                $value = match ($options['type'] ?? null) {
                    'datetime-local' => $value ? Carbon::createFromDatetime((string) $value) : $value,
                    'boolean' => !empty($value),
                    default => $value,
                };

                $config->set($name, $value);
            }
        }
    }

    /**
     * Get the config path
     */
    protected function getConfigPath(string $path = ''): string
    {
        return config_path($path);
    }
}
