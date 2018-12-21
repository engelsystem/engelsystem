<?php

namespace Engelsystem\Renderer;

use Engelsystem\Config\Config as EngelsystemConfig;
use Engelsystem\Container\ServiceProvider;
use Engelsystem\Renderer\Twig\Extensions\Assets;
use Engelsystem\Renderer\Twig\Extensions\Authentication;
use Engelsystem\Renderer\Twig\Extensions\Config;
use Engelsystem\Renderer\Twig\Extensions\Csrf;
use Engelsystem\Renderer\Twig\Extensions\Globals;
use Engelsystem\Renderer\Twig\Extensions\Legacy;
use Engelsystem\Renderer\Twig\Extensions\Markdown;
use Engelsystem\Renderer\Twig\Extensions\Session;
use Engelsystem\Renderer\Twig\Extensions\Translation;
use Engelsystem\Renderer\Twig\Extensions\Url;
use Twig_Environment as Twig;
use Twig_Extension_Core as TwigCore;
use Twig_LoaderInterface as TwigLoaderInterface;

class TwigServiceProvider extends ServiceProvider
{
    /** @var array */
    protected $extensions = [
        'assets'         => Assets::class,
        'authentication' => Authentication::class,
        'config'         => Config::class,
        'csrf'           => Csrf::class,
        'globals'        => Globals::class,
        'session'        => Session::class,
        'legacy'         => Legacy::class,
        'markdown'       => Markdown::class,
        'translation'    => Translation::class,
        'url'            => Url::class,
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
        /** @var EngelsystemConfig $config */
        $config = $this->app->get('config');

        $twigLoader = $this->app->make(TwigLoader::class, ['paths' => $viewsPath]);
        $this->app->instance(TwigLoader::class, $twigLoader);
        $this->app->instance(TwigLoaderInterface::class, $twigLoader);
        $this->app->instance('twig.loader', $twigLoader);

        $cache = $this->app->get('path.cache.views');
        if ($config->get('environment') == 'development') {
            $cache = false;
        }

        $twig = $this->app->make(
            Twig::class,
            [
                'options' => [
                    'cache'            => $cache,
                    'auto_reload'      => true,
                    'strict_variables' => ($config->get('environment') == 'development'),
                ],
            ]
        );
        $this->app->instance(Twig::class, $twig);
        $this->app->instance('twig.environment', $twig);

        /** @var TwigCore $twigCore */
        $twigCore = $twig->getExtension(TwigCore::class);
        $twigCore->setTimezone($config->get('timezone'));

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
