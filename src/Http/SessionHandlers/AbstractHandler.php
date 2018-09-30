<?php

namespace Engelsystem\Http\SessionHandlers;

use SessionHandlerInterface;

abstract class AbstractHandler implements SessionHandlerInterface
{
    /** @var string */
    protected $name;

    /** @var string */
    protected $sessionPath;

    /**
     * Bootstrap the session handler
     *
     * @param string $sessionPath
     * @param string $name
     * @return bool
     */
    public function open($sessionPath, $name): bool
    {
        $this->name = $name;
        $this->sessionPath = $sessionPath;

        return true;
    }

    /**
     * Shutdown the session handler
     *
     * @return bool
     */
    public function close(): bool
    {
        return true;
    }

    /**
     * Remove old sessions
     *
     * @param int $maxLifetime
     * @return bool
     */
    public function gc($maxLifetime): bool
    {
        return true;
    }

    /**
     * Read session data
     *
     * @param string $id
     * @return string
     */
    abstract public function read($id): string;

    /**
     * Write session data
     *
     * @param string $id
     * @param string $data
     * @return bool
     */
    abstract public function write($id, $data): bool;

    /**
     * Delete a session
     *
     * @param string $id
     * @return bool
     */
    abstract public function destroy($id): bool;
}
