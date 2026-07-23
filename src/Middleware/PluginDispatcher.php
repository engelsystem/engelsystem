<?php

declare(strict_types=1);

namespace Engelsystem\Middleware;

use Engelsystem\Application;

class PluginDispatcher extends Dispatcher
{
    public function __construct(Application $app)
    {
        $stack = [];
        foreach ($app->tagged('plugin.middleware') as $middleware) {
            $stack[] = $middleware;
        }

        parent::__construct($stack, $app);
    }
}
