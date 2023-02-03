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

    public function __construct(Application $app, protected ?EventConfig $eventConfig = null)
    {
        parent::__construct($app);
    }

    public function register(): void
    {
        $config = $this->app->make(Config::class);
        $this->app->instance(Config::class, $config);
        $this->app->instance('config', $config);

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
        } catch (QueryException $e) {
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
