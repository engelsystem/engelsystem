<?php

namespace Engelsystem\Exceptions\Handlers;

use Engelsystem\Application;
use Engelsystem\Container\Container;
use Engelsystem\Http\Request;
use Throwable;
use Whoops\Handler\JsonResponseHandler;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Run as WhoopsRunner;

class Whoops extends Legacy implements HandlerInterface
{
    /** @var Application */
    protected $app;

    public function __construct(Container $app)
    {
        $this->app = $app;
    }

    /**
     * @param Request   $request
     * @param Throwable $e
     */
    public function render($request, Throwable $e)
    {
        $whoops = $this->app->make(WhoopsRunner::class);
        $handler = $this->getPrettyPageHandler($e);
        $whoops->pushHandler($handler);

        if ($request->isXmlHttpRequest()) {
            $handler = $this->getJsonResponseHandler();
            $whoops->pushHandler($handler);
        }

        echo $whoops->handleException($e);
    }

    /**
     * @param Throwable $e
     * @return PrettyPageHandler
     */
    protected function getPrettyPageHandler(Throwable $e)
    {
        $handler = $this->app->make(PrettyPageHandler::class);

        $handler->setPageTitle('Just another ' . get_class($e) . ' to fix :(');
        $handler->setApplicationPaths([realpath(__DIR__ . '/../..')]);

        $data = $this->getData();
        $handler->addDataTable('Application', $data);

        return $handler;
    }

    /**
     * @return JsonResponseHandler
     */
    protected function getJsonResponseHandler()
    {
        $handler = $this->app->make(JsonResponseHandler::class);
        $handler->setJsonApi(true);
        $handler->addTraceToOutput(true);

        return $handler;
    }

    /**
     * Aggregate application data
     *
     * @return array
     */
    protected function getData()
    {
        global $user;

        $data = [];
        $data['user'] = $user;
        $data['Booted'] = $this->app->isBooted();

        return $data;
    }
}
