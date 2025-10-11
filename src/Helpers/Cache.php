<?php

declare(strict_types=1);

namespace Engelsystem\Helpers;

class Cache
{
    public function __construct(protected string $path)
    {
    }

    /**
     * Returns the value cached for $key, $default if not
     *
     * If $default is a callable it calls it for the value to be cached
     *
     */
    public function get(string $key, mixed $default = null, int $seconds = 60 * 60): mixed
    {
        $cacheFile = $this->cacheFilePath($key);

        // Check for file existence, forget old ones
        $exists = file_exists($cacheFile);
        if ($exists && filemtime($cacheFile) < time() - $seconds) {
            $this->forget($key);
            $exists = false;
        }

        // Handle callback to get default value
        if (!$exists) {
            if (!is_callable($default)) {
                return $default;
            }

            file_put_contents($cacheFile, serialize($default()));
        }

        // Get data from cache
        return unserialize(file_get_contents($cacheFile));
    }

    public function forget(string $key): void
    {
        $cacheFile = $this->cacheFilePath($key);
        if (!file_exists($cacheFile)) {
            return;
        }

        unlink($cacheFile);
    }

    protected function cacheFilePath(string $key): string
    {
        return $this->path . '/' . $key . '.cache';
    }
}
