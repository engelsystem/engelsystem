<?php

namespace Engelsystem\Config;

use Engelsystem\Application;
use Engelsystem\Container\ServiceProvider;
use Engelsystem\Models\EventConfig;
use Exception;
use Illuminate\Database\QueryException;

class ConfigServiceProvider extends ServiceProvider
{
    /** @var array */
    protected $configFiles = ['config.default.php', 'config.php'];

    /** @var EventConfig */
    protected $eventConfig;

    /**
     * @param Application $app
     * @param EventConfig $eventConfig
     */
    public function __construct(Application $app, EventConfig $eventConfig = null)
    {
        parent::__construct($app);

        $this->eventConfig = $eventConfig;
    }

    public function register()
    {
        $config = $this->app->make(Config::class);
        $this->app->instance(Config::class, $config);
        $this->app->instance('config', $config);

        foreach ($this->configFiles as $file) {
            $file = $this->getConfigPath($file);

            if (!file_exists($file)) {
                continue;
            }

            $config->set(array_replace_recursive(
                $config->get(null),
                require $file
            ));
        }

        if (empty($config->get(null))) {
            throw new Exception('Configuration not found');
        }
    }

    public function boot()
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
     *
     * @param string $path
     * @return string
     */
    protected function getConfigPath($path = ''): string
    {
        return config_path($path);
    }
}
