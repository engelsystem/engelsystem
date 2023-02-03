<?php

declare(strict_types=1);

namespace Engelsystem\Renderer;

use Engelsystem\Container\ServiceProvider;

class RendererServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->registerRenderer();
        $this->registerHtmlEngine();
    }

    public function boot(): void
    {
        $renderer = $this->app->get('renderer');

        foreach ($this->app->tagged('renderer.engine') as $engine) {
            $renderer->addRenderer($engine);
        }
    }

    protected function registerRenderer(): void
    {
        $renderer = $this->app->make(Renderer::class);
        $this->app->instance(Renderer::class, $renderer);
        $this->app->instance('renderer', $renderer);
    }

    protected function registerHtmlEngine(): void
    {
        $htmlEngine = $this->app->make(HtmlEngine::class);
        $this->app->instance(HtmlEngine::class, $htmlEngine);
        $this->app->instance('renderer.htmlEngine', $htmlEngine);
        $this->app->tag('renderer.htmlEngine', ['renderer.engine']);
    }
}
