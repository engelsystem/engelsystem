<?php

namespace Engelsystem\Http;

use ErrorException;

class Request
{
    /** @var self */
    protected static $instance;

    /** @var array of GET data */
    protected $query;

    /** @var array of POST data */
    protected $request;

    /** @var array of SERVER data */
    protected $server;

    /** @var string */
    protected $scheme;

    /** @var string */
    protected $host;

    /** @var string */
    protected $baseUrl = '';

    /** @var string */
    protected $path;

    /**
     * Initialize request
     *
     * @param array  $query   The GET data
     * @param array  $request the POST data
     * @param array  $server  the SERVER data
     * @param string $baseUrl base url to use for links
     */
    public function create(array $query, array $request, array $server, $baseUrl = null)
    {
        $this->query = $query;
        $this->request = $request;
        $this->server = array_merge([
            'SERVER_NAME' => 'localhost',
            'HTTP_HOST'   => 'localhost',
            'SERVER_PORT' => 80,
            'REQUEST_URI' => '/',
        ], $server);

        if (isset($this->server['HTTPS']) && $this->server['HTTPS'] == 'off') {
            unset($this->server['HTTPS']);
        }

        $uri = $this->server['REQUEST_URI'];
        $uri = '/' . ltrim($uri, '/');
        $uri = explode('?', $uri);
        $this->path = array_shift($uri);

        $components = parse_url($baseUrl);
        if (!$components) {
            $components = [];
        }

        $this->scheme = (isset($components['scheme']) ? $components['scheme'] : ($this->isSecure() ? 'https' : 'http'));
        $this->host = (isset($components['host']) ? $components['host'] : $this->server['SERVER_NAME']);

        if (isset($components['path'])) {
            $this->baseUrl = '/' . ltrim($components['path'], '/');
            $this->path = preg_replace('~^' . preg_quote($this->baseUrl, '~') . '~i', '', $this->path);
            $this->path = '/' . ltrim($this->path, '/');
        }
    }

    public function isSecure()
    {
        return isset($this->server['HTTPS']);
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
     * Get the requested path
     *
     * @return string
     */
    public function path()
    {
        // @TODO: base uri?
        return $this->path;
    }

    public function url()
    {
        return $this->getSchemeAndHttpHost() . $this->getBaseUrl() . '/' . $this->path();
    }

    /**
     * @return string
     */
    public function root()
    {
        return $this->baseUrl;
    }

    public function getSchemeAndHttpHost()
    {
        return $this->getScheme() . '://' . $this->getHttpHost();
    }

    public function getScheme()
    {
        return $this->scheme;
    }

    public function getHttpHost()
    {
        return $this->host;
    }

    public function getBaseUrl()
    {
        return $this->baseUrl;
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
