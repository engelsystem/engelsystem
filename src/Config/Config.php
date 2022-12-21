<?php

namespace Engelsystem\Config;

use Illuminate\Support\Fluent;

class Config extends Fluent
{
    /**
     * The config values
     */
    protected $attributes = []; // phpcs:ignore

    /**
     * @param string|array $key
     */
    public function get(mixed $key, mixed $default = null): mixed
    {
        if (is_null($key)) {
            return $this->attributes;
        }

        if ($this->has($key)) {
            return $this->attributes[$key];
        }

        return $default;
    }

    /**
     * @param string|array $key
     */
    public function set(mixed $key, mixed $value = null): void
    {
        if (is_array($key)) {
            foreach ($key as $configKey => $configValue) {
                $this->set($configKey, $configValue);
            }

            return;
        }

        $this->attributes[$key] = $value;
    }

    /**
     * @param string $key
     */
    public function has(mixed $key): bool
    {
        return $this->offsetExists($key);
    }

    /**
     * @param string $key
     */
    public function remove(mixed $key): void
    {
        $this->offsetUnset($key);
    }
}
