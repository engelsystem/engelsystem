<?php

namespace Engelsystem\Http;

use ErrorException;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

class Request extends SymfonyRequest
{
    /** @var self */
    protected static $instance;

    /**
     * Get POST input
     *
     * @param string $key
     * @param mixed  $default
     * @return mixed
     */
    public function postData($key, $default = null)
    {
        return $this->request->get($key, $default);
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
        return $this->get($key, $default);
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
     * Get the requested path
     *
     * @return string
     */
    public function path()
    {
        $pattern = trim($this->getPathInfo(), '/');

        return $pattern == '' ? '/' : $pattern;
    }

    /**
     * Return the current URL
     *
     * @return string
     */
    public function url()
    {
        return rtrim(preg_replace('/\?.*/', '', $this->getUri()), '/');
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
