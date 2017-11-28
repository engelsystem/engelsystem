<?php

namespace Engelsystem\Config;

use Engelsystem\Container\ServiceProvider;

class ConfigServiceProvider extends ServiceProvider
{
    public function register()
    {
        $defaultConfigFile = config_path('config.default.php');
        $configFile = config_path('config.php');

        $config = $this->app->make(Config::class);
        $this->app->instance('config', $config);

        $config->set(require $defaultConfigFile);

        if (file_exists($configFile)) {
            $config->set(array_replace_recursive(
                $config->get(null),
                require $configFile
            ));
        }
    }
}
