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
     * @param string|null $key
     * @param mixed       $default
     * @return mixed
     */
    public function get($key, $default = null)
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
     * @param mixed        $value
     */
    public function set($key, $value = null)
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
    public function has($key)
    {
        return $this->offsetExists($key);
    }

    /**
     * @param string $key
     */
    public function remove($key)
    {
        $this->offsetUnset($key);
    }
}
