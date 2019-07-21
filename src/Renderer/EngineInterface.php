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
    public function get(string $path, array $data = []): string;

    /**
     * @param string $path
     * @return bool
     */
    public function canRender(string $path): bool;

    /**
     * @param string|mixed[] $key
     * @param mixed          $value
     */
    public function share($key, $value = null);
}
