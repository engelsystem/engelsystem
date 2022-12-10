<?php

namespace Engelsystem\Http\SessionHandlers;

use SessionHandlerInterface;

abstract class AbstractHandler implements SessionHandlerInterface
{
    protected string $name;

    protected string $sessionPath;

    /**
     * Bootstrap the session handler
     */
    public function open(string $path, string $name): bool
    {
        $this->name = $name;
        $this->sessionPath = $path;

        return true;
    }

    /**
     * Shutdown the session handler
     */
    public function close(): bool
    {
        return true;
    }

    /**
     * Remove old sessions
     */
    public function gc(int $max_lifetime): int|false
    {
        return 0;
    }

    /**
     * Read session data
     */
    abstract public function read(string $id): string;

    /**
     * Write session data
     */
    abstract public function write(string $id, string $data): bool;

    /**
     * Delete a session
     */
    abstract public function destroy(string $id): bool;
}
