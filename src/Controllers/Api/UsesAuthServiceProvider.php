<?php

declare(strict_types=1);

namespace Engelsystem\Controllers\Api;

use Engelsystem\Application;
use Engelsystem\Container\ServiceProvider;
use Engelsystem\Helpers\Authenticator;

class UsesAuthServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->afterResolving(function ($object, Application $app): void {
            if (!$object instanceof ApiController || !method_exists($object, 'setAuth')) {
                return;
            }

            /** @var UsesAuth $object */
            $object->setAuth($app->get(Authenticator::class));
        });
    }
}
