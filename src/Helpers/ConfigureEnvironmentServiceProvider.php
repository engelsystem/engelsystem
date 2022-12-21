<?php

namespace Engelsystem\Helpers;

use Carbon\CarbonTimeZone;
use Engelsystem\Config\Config;
use Engelsystem\Container\ServiceProvider;
use Engelsystem\Exceptions\Handler;
use Engelsystem\Exceptions\Handlers\HandlerInterface;

class ConfigureEnvironmentServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        /** @var Config $config */
        $config = $this->app->get('config');

        $timezone = new CarbonTimeZone($config->get('timezone'));
        $this->setTimeZone($timezone);

        $this->displayErrors(false);
        if ($config->get('environment') == 'development') {
            $this->displayErrors(true);
            $this->errorReporting(E_ALL);
            $this->setupDevErrorHandler();
        }
    }

    /**
     * @codeCoverageIgnore
     */
    protected function setTimeZone(CarbonTimeZone $timeZone): void
    {
        ini_set('date.timezone', (string)$timeZone);
        date_default_timezone_set($timeZone);
    }

    /**
     * @codeCoverageIgnore
     */
    protected function displayErrors(bool $displayErrors): void
    {
        ini_set('display_errors', $displayErrors);
    }

    /**
     * @codeCoverageIgnore
     */
    protected function errorReporting(int $errorReporting): void
    {
        error_reporting($errorReporting);
    }

    /**
     * Setup the development error handler
     */
    protected function setupDevErrorHandler(): void
    {
        /** @var Handler $errorHandler */
        $errorHandler = $this->app->get('error.handler');
        $errorHandler->setEnvironment(Handler::ENV_DEVELOPMENT);
        $this->app->bind(HandlerInterface::class, 'error.handler.development');
    }
}
