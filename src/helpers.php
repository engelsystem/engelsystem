<?php
// Some useful functions

use Engelsystem\Config\Config;
use Engelsystem\Http\Request;

/**
 * Get or set config values
 *
 * @param string|array $key
 * @param mixed        $default
 * @return mixed|Config
 */
function config($key = null, $default = null)
{
    if (empty($key)) {
        return Config::getInstance();
    }

    if (is_array($key)) {
        Config::getInstance()->set($key);
    }

    return Config::getInstance()->get($key, $default);
}

/**
 * @param string $key
 * @param mixed  $default
 * @return Request|mixed
 */
function request($key = null, $default = null)
{
    $request = Request::getInstance();

    if (is_null($key)) {
        return $request;
    }

    return $request->input($key, $default);
}
