<?php

namespace Engelsystem\Http\Validation;

use Engelsystem\Application;
use Engelsystem\Container\ServiceProvider;
use Engelsystem\Controllers\BaseController;

class ValidationServiceProvider extends ServiceProvider
{
    public function register()
    {
        $validator = $this->app->make(Validator::class);
        $this->app->instance(Validator::class, $validator);
        $this->app->instance('validator', $validator);

        $this->app->afterResolving(function ($object, Application $app) {
            if (!$object instanceof BaseController) {
                return;
            }

            $object->setValidator($app->get(Validator::class));
        });
    }
}
