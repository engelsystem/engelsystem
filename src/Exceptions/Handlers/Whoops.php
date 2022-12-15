<?php

namespace Engelsystem\Exceptions\Handlers;

use Engelsystem\Application;
use Engelsystem\Container\Container;
use Engelsystem\Helpers\Authenticator;
use Engelsystem\Http\Request;
use Throwable;
use Whoops\Handler\JsonResponseHandler;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Run as WhoopsRunner;

class Whoops extends Legacy implements HandlerInterface
{
    protected Application $app;

    /**
     * Whoops constructor.
     */
    public function __construct(Container $app)
    {
        $this->app = $app;
    }

    public function render(Request $request, Throwable $e): void
    {
        $whoops = $this->app->make(WhoopsRunner::class);
        $handler = $this->getPrettyPageHandler($e);
        $whoops->pushHandler($handler);
        $whoops->writeToOutput(false);
        $whoops->allowQuit(false);

        if ($request->isXmlHttpRequest()) {
            $handler = $this->getJsonResponseHandler();
            $whoops->pushHandler($handler);
        }

        echo $whoops->handleException($e);
    }

    protected function getPrettyPageHandler(Throwable $e): PrettyPageHandler
    {
        /** @var PrettyPageHandler $handler */
        $handler = $this->app->make(PrettyPageHandler::class);

        $handler->setPageTitle('Just another ' . get_class($e) . ' to fix :(');
        $handler->setApplicationPaths([
            realpath(__DIR__ . '/../..'),
            realpath(__DIR__ . '/../../../includes/'),
            realpath(__DIR__ . '/../../../db/'),
            realpath(__DIR__ . '/../../../tests/'),
            realpath(__DIR__ . '/../../../public/'),
        ]);

        $data = $this->getData();
        $handler->addDataTable('Application', $data);

        return $handler;
    }

    protected function getJsonResponseHandler(): JsonResponseHandler
    {
        $handler = $this->app->make(JsonResponseHandler::class);
        $handler->setJsonApi(true);
        $handler->addTraceToOutput(true);

        return $handler;
    }

    /**
     * Aggregate application data
     */
    protected function getData(): array
    {
        $data = [];
        $user = null;

        if ($this->app->has('authenticator')) {
            /** @var Authenticator $authenticator */
            $authenticator = $this->app->get('authenticator');
            $user = $authenticator->user();
        }

        $data['user'] = $user;
        $data['Booted'] = $this->app->isBooted();

        return $data;
    }
}
