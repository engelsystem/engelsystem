<?php

namespace Engelsystem\Renderer;

use Engelsystem\Container\ServiceProvider;
use Engelsystem\Renderer\Twig\Extensions\Config;
use Engelsystem\Renderer\Twig\Extensions\Globals;
use Engelsystem\Renderer\Twig\Extensions\Session;
use Engelsystem\Renderer\Twig\Extensions\Translation;
use Engelsystem\Renderer\Twig\Extensions\Url;
use Twig_Environment as Twig;
use Twig_LoaderInterface as TwigLoaderInterface;

class TwigServiceProvider extends ServiceProvider
{
    /** @var array */
    protected $extensions = [
        'config'      => Config::class,
        'globals'     => Globals::class,
        'session'     => Session::class,
        'url'         => Url::class,
        'translation' => Translation::class,
    ];

    public function register()
    {
        $this->registerTwigEngine();

        foreach ($this->extensions as $alias => $class) {
            $this->registerTwigExtensions($class, $alias);
        }
    }

    public function boot()
    {
        /** @var Twig $renderer */
        $renderer = $this->app->get('twig.environment');

        foreach ($this->app->tagged('twig.extension') as $extension) {
            $renderer->addExtension($extension);
        }
    }

    protected function registerTwigEngine()
    {
        $viewsPath = $this->app->get('path.views');

        $twigLoader = $this->app->make(TwigLoader::class, ['paths' => $viewsPath]);
        $this->app->instance(TwigLoader::class, $twigLoader);
        $this->app->instance(TwigLoaderInterface::class, $twigLoader);
        $this->app->instance('twig.loader', $twigLoader);

        $twig = $this->app->make(Twig::class);
        $this->app->instance(Twig::class, $twig);
        $this->app->instance('twig.environment', $twig);

        $twigEngine = $this->app->make(TwigEngine::class);
        $this->app->instance('renderer.twigEngine', $twigEngine);
        $this->app->tag('renderer.twigEngine', ['renderer.engine']);
    }

    /**
     * @param string $class
     * @param string $alias
     */
    protected function registerTwigExtensions($class, $alias)
    {
        $alias = 'twig.extension.' . $alias;

        $extension = $this->app->make($class);

        $this->app->instance($class, $extension);
        $this->app->instance($alias, $extension);

        $this->app->tag($alias, ['twig.extension']);
    }
}
