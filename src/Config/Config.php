<?php

namespace Engelsystem\Config;

use Illuminate\Support\Fluent;

class Config extends Fluent
{
    /**
     * The config values
     *
     * @var array
     */
    protected $attributes = [];

    /**
     * @param string|array $key
     */
    public function get(mixed $key, mixed $default = null)
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
    public function set(mixed $key, mixed $value = null)
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
     * @return bool
     */
    public function has(mixed $key)
    {
        return $this->offsetExists($key);
    }

    /**
     * @param string $key
     */
    public function remove(mixed $key)
    {
        $this->offsetUnset($key);
    }
}
