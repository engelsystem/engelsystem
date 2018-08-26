<?php

namespace Engelsystem\Renderer;

use Engelsystem\Container\ServiceProvider;
use Twig_Environment as Twig;
use Twig_LoaderInterface as TwigLoaderInterface;

class TwigServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->registerTwigEngine();
    }

    protected function registerTwigEngine()
    {
        $viewsPath = $this->app->get('path.views');

        $twigLoader = $this->app->make(TwigLoader::class, ['paths' => $viewsPath]);
        $this->app->instance(TwigLoader::class, $twigLoader);
        $this->app->instance(TwigLoaderInterface::class, $twigLoader);

        $twig = $this->app->make(Twig::class);
        $this->app->instance(Twig::class, $twig);

        $twigEngine = $this->app->make(TwigEngine::class);
        $this->app->instance('renderer.twigEngine', $twigEngine);
        $this->app->tag('renderer.twigEngine', ['renderer.engine']);
    }
}
