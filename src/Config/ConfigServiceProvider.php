<?php

declare(strict_types=1);

namespace Engelsystem\Config;

use Engelsystem\Application;
use Engelsystem\Container\ServiceProvider;
use Engelsystem\Models\EventConfig;
use Exception;
use Illuminate\Database\QueryException;

class ConfigServiceProvider extends ServiceProvider
{
    protected array $configFiles = ['app.php', 'config.default.php', 'config.php'];

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

    public function __construct(Application $app, protected ?EventConfig $eventConfig = null)
    {
        parent::__construct($app);
    }

    public function register(): void
    {
        $config = $this->app->make(Config::class);
        $this->app->instance(Config::class, $config);
        $this->app->instance('config', $config);

        // Load configuration from files
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

        if (empty($config->get(null))) {
            throw new Exception('Configuration not found');
        }

        // Prune values with null to remove them
        foreach ($this->configVarsToPruneNulls as $key) {
            $config->set($key, array_filter($config->get($key), function ($v) {
                return !is_null($v);
            }));
        }
    }

    public function boot(): void
    {
        if (!$this->eventConfig) {
            return;
        }

        /** @var Config $config */
        $config = $this->app->get('config');
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

    /**
     * Get the config path
     */
    protected function getConfigPath(string $path = ''): string
    {
        return config_path($path);
    }
}
