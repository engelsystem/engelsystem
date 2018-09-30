<?php

namespace Engelsystem\Test\Unit\Http\SessionHandlers\Stub;

use Engelsystem\Http\SessionHandlers\AbstractHandler;

class ArrayHandler extends AbstractHandler
{
    /** @var string[] */
    protected $content = [];

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

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getSessionPath(): string
    {
        return $this->sessionPath;
    }
}
