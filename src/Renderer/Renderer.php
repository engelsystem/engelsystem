<?php

namespace Engelsystem\Renderer;

use Psr\Log\LoggerAwareTrait;

class Renderer
{
    use LoggerAwareTrait;

    /** @var EngineInterface[] */
    protected array $renderer = [];

    /**
     * Render a template
     *
     * @param mixed[] $data
     */
    public function render(string $template, array $data = []): string
    {
        foreach ($this->renderer as $renderer) {
            if (!$renderer->canRender($template)) {
                continue;
            }

            return $renderer->get($template, $data);
        }

        if ($this->logger) {
            $this->logger->critical('Unable to find a renderer for template file "{file}"', ['file' => $template]);
        }

        return '';
    }

    /**
     * Add a new renderer engine
     */
    public function addRenderer(EngineInterface $renderer): void
    {
        $this->renderer[] = $renderer;
    }
}
