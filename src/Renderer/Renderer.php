<?php

namespace Engelsystem\Renderer;

use ErrorException;

class Renderer
{
    /** @var self */
    protected static $instance;

    /** @var EngineInterface[] */
    protected $renderer = [];

    /**
     * Render a template
     *
     * @param string  $template
     * @param mixed[] $data
     * @return string
     */
    public function render($template, $data = [])
    {
        foreach ($this->renderer as $renderer) {
            if (!$renderer->canRender($template)) {
                continue;
            }

            return $renderer->get($template, $data);
        }

        engelsystem_error('Unable to find a renderer for template file &laquo;' . $template . '&raquo;.');
        return '';
    }

    /**
     * Add a new renderer engine
     *
     * @param EngineInterface $renderer
     */
    public function addRenderer(EngineInterface $renderer)
    {
        $this->renderer[] = $renderer;
    }

    /**
     * @return self
     * @throws ErrorException
     */
    public static function getInstance()
    {
        return self::$instance;
    }

    /**
     * @param self $instance
     */
    public static function setInstance($instance)
    {
        self::$instance = $instance;
    }
}
