<?php

namespace Engelsystem\Config;

use Illuminate\Support\Fluent;

class Config extends Fluent
{
    /**
     * The config values
     */
    protected $attributes = []; // phpcs:ignore

    public function get(mixed $key, mixed $default = null): mixed
    {
        if (is_null($key)) {
            return $this->attributes;
        }

        return parent::get($key, $default);
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

        parent::set($key, $value);
    }

    /**
     * @param string $key
     */
    public function has(mixed $key): bool
    {
        return data_has($this->attributes, $key);
    }

    /**
     * @param string $key
     */
    public function remove(mixed $key): void
    {
        data_forget($this->attributes, $key);
    }
}
