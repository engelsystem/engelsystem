<?php

namespace Engelsystem\Config;

use ErrorException;

class Config
{
    /**
     * @var self
     */
    protected static $instance;

    /**
     * The config values
     *
     * @var array
     */
    protected $data = [];

    /**
     * @param string|null $key
     * @param mixed       $default
     * @return mixed
     */
    public function get($key, $default = null)
    {
        if (is_null($key)) {
            return $this->data;
        }

        if ($this->has($key)) {
            return $this->data[$key];
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

        $this->data[$key] = $value;
    }

    /**
     * @param string $key
     * @return bool
     */
    public function has($key)
    {
        return isset($this->data[$key]);
    }

    /**
     * @param string $key
     */
    public function remove($key)
    {
        unset($this->data[$key]);
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function __get($key)
    {
        return $this->get($key);
    }

    /**
     * @param string $key
     * @param mixed  $value
     */
    public function __set($key, $value)
    {
        $this->set($key, $value);
    }

    /**
     * @param string $key
     * @return bool
     */
    public function __isset($key)
    {
        return $this->has($key);
    }

    /**
     * @param string $key
     */
    public function __unset($key)
    {
        $this->remove($key);
    }

    /**
     * @return Config
     * @throws ErrorException
     */
    public static function getInstance()
    {
        if (!self::$instance instanceof self) {
            throw new ErrorException('Config not initialized');
        }

        return self::$instance;
    }

    /**
     * @param self $instance
     */
    public static function setInstance($instance)
    {
        self::$instance = $instance;
    }
}
