<?php

declare(strict_types=1);

namespace Engelsystem\Renderer;

interface EngineInterface
{
    /**
     * Render a template
     *
     * @param mixed[] $data
     */
    public function get(string $path, array $data = []): string;

    /**
     * Check if the engine can render the specified template
     */
    public function canRender(string $path): bool;

    /**
     * Add shared variables
     *
     * @param string|mixed[] $key
     */
    public function share(string|array $key, mixed $value = null): void;
}
