<?php

namespace Engelsystem\Test\Unit\Renderer\Stub;

use Engelsystem\Renderer\Engine;

class EngineImplementation extends Engine
{
    /**
     * @inheritdoc
     */
    public function get(string $path, array $data = []): string
    {
        return '';
    }

    /**
     * @inheritdoc
     */
    public function canRender(string $path): bool
    {
        return true;
    }

    /**
     * @return array
     */
    public function getSharedData(): array
    {
        return $this->sharedData;
    }
}
