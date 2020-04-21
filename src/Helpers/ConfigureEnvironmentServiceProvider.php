<?php

namespace Engelsystem\Helpers;

use Carbon\CarbonTimeZone;
use Engelsystem\Config\Config;
use Engelsystem\Container\ServiceProvider;

class ConfigureEnvironmentServiceProvider extends ServiceProvider
{
    public function register()
    {
        /** @var Config $config */
        $config = $this->app->get('config');

        $timezone = new CarbonTimeZone($config->get('timezone'));
        $this->setTimeZone($timezone);
    }

    /**
     * @param CarbonTimeZone $timeZone
     * @codeCoverageIgnore
     */
    protected function setTimeZone(CarbonTimeZone $timeZone)
    {
        ini_set('date.timezone', $timeZone);
        date_default_timezone_set($timeZone);
    }
}
