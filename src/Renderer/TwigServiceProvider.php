<?php

declare(strict_types=1);

namespace Engelsystem\Renderer;

use Engelsystem\Config\Config as EngelsystemConfig;
use Engelsystem\Container\ServiceProvider;
use Engelsystem\Renderer\Twig\Extensions\Assets;
use Engelsystem\Renderer\Twig\Extensions\Authentication;
use Engelsystem\Renderer\Twig\Extensions\Config;
use Engelsystem\Renderer\Twig\Extensions\Csrf;
use Engelsystem\Renderer\Twig\Extensions\Develop;
use Engelsystem\Renderer\Twig\Extensions\Globals;
use Engelsystem\Renderer\Twig\Extensions\Legacy;
use Engelsystem\Renderer\Twig\Extensions\Markdown;
use Engelsystem\Renderer\Twig\Extensions\Notification;
use Engelsystem\Renderer\Twig\Extensions\Session;
use Engelsystem\Renderer\Twig\Extensions\Translation;
use Engelsystem\Renderer\Twig\Extensions\Url;
use Engelsystem\Renderer\Twig\Extensions\Uuid;
use Symfony\Component\VarDumper\VarDumper;
use Twig\Environment as Twig;
use Twig\Extension\CoreExtension as TwigCore;
use Twig\Loader\LoaderInterface as TwigLoaderInterface;
use TwigBridge\Extension\Laravel\Model as TwigModel;

class TwigServiceProvider extends ServiceProvider
{
    /** @var array<string, class-string> */
    protected array $extensions = [
        'assets'         => Assets::class,
        'authentication' => Authentication::class,
        'config'         => Config::class,
        'csrf'           => Csrf::class,
        'develop'        => Develop::class,
        'globals'        => Globals::class,
        'notification'   => Notification::class,
        'twigmodel'      => TwigModel::class,
        'session'        => Session::class,
        'legacy'         => Legacy::class,
        'markdown'       => Markdown::class,
        'translation'    => Translation::class,
        'url'            => Url::class,
        'uuid'           => Uuid::class,
    ];

    public function register(): void
    {
        $this->registerTwigEngine();

        foreach ($this->extensions as $alias => $class) {
            $this->registerTwigExtensions($class, $alias);
        }
    }

    public function boot(): void
    {
        /** @var Twig $renderer */
        $renderer = $this->app->get('twig.environment');

        foreach ($this->app->tagged('twig.extension') as $extension) {
            $renderer->addExtension($extension);
        }

        if (class_exists(VarDumper::class)) {
            /** @var Develop $dev */
            $dev = $this->app->get('twig.extension.develop');
            $dev->setDumper($this->app->make(VarDumper::class));
        }
    }

    protected function registerTwigEngine(): void
    {
        $viewsPath = $this->app->get('path.views');
        /** @var EngelsystemConfig $config */
        $config = $this->app->get('config');

        $twigLoader = $this->app->make(TwigLoader::class, ['paths' => $viewsPath]);
        $this->app->instance(TwigLoader::class, $twigLoader);
        $this->app->instance(TwigLoaderInterface::class, $twigLoader);
        $this->app->instance('twig.loader', $twigLoader);

        $cache = $this->app->get('path.cache.views');
        $twigDebug = false;
        if ($config->get('environment') == 'development') {
            $cache = false;
            $twigDebug = true;
        }

        $twig = $this->app->make(
            Twig::class,
            [
                'options' => [
                    'cache'            => $cache,
                    'auto_reload'      => true,
                    'debug'            => $twigDebug,
                    'strict_variables' => $twigDebug,
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

    protected function registerTwigExtensions(string $class, string $alias): void
    {
        $alias = 'twig.extension.' . $alias;

        $extension = $this->app->make($class);

        $this->app->instance($class, $extension);
        $this->app->instance($alias, $extension);

        $this->app->tag($alias, ['twig.extension']);
    }
}
