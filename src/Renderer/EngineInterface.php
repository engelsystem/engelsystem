<?php

namespace Engelsystem\Renderer;

interface EngineInterface
{
    /**
     * Render a template
     *
     * @param mixed[] $data
     */
    public function get(string $path, array $data = []): string;

    public function canRender(string $path): bool;

    /**
     * @param string|mixed[] $key
     */
    public function share(string|array $key, mixed $value = null): void;
}
