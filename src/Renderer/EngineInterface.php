<?php

namespace Engelsystem\Renderer;

interface EngineInterface
{
    /**
     * Render a template
     *
     * @param string  $path
     * @param mixed[] $data
     * @return string
     */
    public function get($path, $data = []);

    /**
     * @param string $path
     * @return bool
     */
    public function canRender($path);
}
