<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Http\SessionHandlers\Stub;

use Engelsystem\Http\SessionHandlers\AbstractHandler;

class ArrayHandler extends AbstractHandler
{
    /** @var string[] */
    protected array $content = [];

    /**
     * {@inheritdoc}
     */
    public function read($id): string
    {
        if (isset($this->content[$id])) {
            return $this->content[$id];
        }

        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function write($id, $data): bool
    {
        $this->content[$id] = $data;

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function destroy($id): bool
    {
        unset($this->content[$id]);

        return true;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getSessionPath(): string
    {
        return $this->sessionPath;
    }
}
