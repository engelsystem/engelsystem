<?php

namespace Engelsystem\Http;

use ErrorException;

class Request
{
    /** @var self */
    protected static $instance;

    /** @var array of POST data */
    protected $request;

    /** @var array of GET data */
    protected $query;

    /**
     * Initialize request
     */
    public function create()
    {
        $this->request = $_POST;
        $this->query = $_GET;
    }

    /**
     * Get GET input
     *
     * @param string $key
     * @param mixed  $default
     * @return mixed
     */
    public function get($key, $default = null)
    {
        if (!empty($this->query[$key])) {
            return $this->query[$key];
        }

        return $default;
    }

    /**
     * Get POST input
     *
     * @param string $key
     * @param mixed  $default
     * @return mixed
     */
    public function post($key, $default = null)
    {
        if (!empty($this->request[$key])) {
            return $this->request[$key];
        }

        return $default;
    }

    /**
     * Get input data
     *
     * @param string $key
     * @param mixed  $default
     * @return mixed
     */
    public function input($key, $default = null)
    {
        $data = $this->request + $this->query;

        if (!empty($data[$key])) {
            return $data[$key];
        }

        return $default;
    }

    /**
     * Checks if the input exists
     *
     * @param string $key
     * @return bool
     */
    public function has($key)
    {
        $value = $this->input($key);

        return !empty($value);
    }

    /**
     * @return self
     * @throws ErrorException
     */
    public static function getInstance()
    {
        if (!self::$instance instanceof self) {
            throw new ErrorException('Request not initialized');
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
