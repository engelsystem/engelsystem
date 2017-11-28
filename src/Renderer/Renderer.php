<?php

namespace Engelsystem\Renderer;

use Psr\Log\LoggerAwareTrait;

class Renderer
{
    use LoggerAwareTrait;

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

        if ($this->logger) {
            $this->logger->error('Unable to find a renderer for template file "{file}"', ['file' => $template]);
        }

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
}
