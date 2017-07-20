<?php
// Some useful functions

use Engelsystem\Config\Config;
use Engelsystem\Http\Request;
use Engelsystem\Renderer\Renderer;

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

/**
 * @param string  $template
 * @param mixed[] $data
 * @return Renderer|string
 */
function view($template = null, $data = null)
{
    $renderer = Renderer::getInstance();

    if (is_null($template)) {
        return $renderer;
    }

    return $renderer->render($template, $data);
}
