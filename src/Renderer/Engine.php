<?php

declare(strict_types=1);

namespace Engelsystem\Renderer;

abstract class Engine implements EngineInterface
{
    protected array $sharedData = [];

    /**
     * @param mixed[]|string $key
     * @param null           $value
     */
    public function share(array|string $key, $value = null): void
    {
        if (!is_array($key)) {
            $key = [$key => $value];
        }

        $this->sharedData = array_replace_recursive($this->sharedData, $key);
    }
}
