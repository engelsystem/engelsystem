<?php

namespace Engelsystem\Renderer;

use Engelsystem\Container\ServiceProvider;

class RendererServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->registerRenderer();
        $this->registerHtmlEngine();
    }

    public function boot()
    {
        $renderer = $this->app->get('renderer');

        foreach ($this->app->tagged('renderer.engine') as $engine) {
            $renderer->addRenderer($engine);
        }
    }

    protected function registerRenderer()
    {
        $renderer = $this->app->make(Renderer::class);
        $this->app->instance(Renderer::class, $renderer);
        $this->app->instance('renderer', $renderer);
    }

    protected function registerHtmlEngine()
    {
        $htmlEngine = $this->app->make(HtmlEngine::class);
        $this->app->instance(HtmlEngine::class, $htmlEngine);
        $this->app->instance('renderer.htmlEngine', $htmlEngine);
        $this->app->tag('renderer.htmlEngine', ['renderer.engine']);
    }
}
