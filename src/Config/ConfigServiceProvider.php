<?php

namespace Engelsystem\Config;

use Engelsystem\Container\ServiceProvider;
use Exception;

class ConfigServiceProvider extends ServiceProvider
{
    /** @var array */
    protected $configFiles = ['config.default.php', 'config.php'];

    public function register()
    {
        $config = $this->app->make(Config::class);
        $this->app->instance(Config::class, $config);
        $this->app->instance('config', $config);

        foreach ($this->configFiles as $file) {
            $file = config_path($file);

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
}
