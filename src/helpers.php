<?php
// Some useful functions

use Engelsystem\Application;
use Engelsystem\Config\Config;
use Engelsystem\Http\Request;
use Engelsystem\Renderer\Renderer;
use Engelsystem\Routing\UrlGeneratorInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Get the global app instance
 *
 * @param string $id
 * @return mixed
 */
function app($id = null)
{
    if (is_null($id)) {
        return Application::getInstance();
    }

    return Application::getInstance()->get($id);
}

/**
 * @param string $path
 * @return string
 */
function base_path($path = '')
{
    return app('path') . (empty($path) ? '' : DIRECTORY_SEPARATOR . $path);
}

/**
 * Get or set config values
 *
 * @param string|array $key
 * @param mixed        $default
 * @return mixed|Config
 */
function config($key = null, $default = null)
{
    $config = app('config');

    if (empty($key)) {
        return $config;
    }

    if (is_array($key)) {
        $config->set($key);
        return true;
    }

    return $config->get($key, $default);
}

/**
 * @param string $path
 * @return string
 */
function config_path($path = '')
{
    return app('path.config') . (empty($path) ? '' : DIRECTORY_SEPARATOR . $path);
}

/**
 * @param string $key
 * @param mixed  $default
 * @return mixed
 */
function env($key, $default = null)
{
    $value = getenv($key);
    if ($value === false) {
        return $default;
    }

    return $value;
}

/**
 * @param string $key
 * @param mixed  $default
 * @return Request|mixed
 */
function request($key = null, $default = null)
{
    $request = app('request');

    if (is_null($key)) {
        return $request;
    }

    return $request->input($key, $default);
}

/**
 * @param string $key
 * @param mixed  $default
 * @return SessionInterface|mixed
 */
function session($key = null, $default = null)
{
    $session = app('session');

    if (is_null($key)) {
        return $session;
    }

    return $session->get($key, $default);
}

/**
 * @param string $path
 * @param array  $parameters
 * @return UrlGeneratorInterface|string
 */
function url($path = null, $parameters = [])
{
    $urlGenerator = app('routing.urlGenerator');

    if (is_null($path)) {
        return $urlGenerator;
    }

    return $urlGenerator->link_to($path, $parameters);
}

/**
 * @param string  $template
 * @param mixed[] $data
 * @return Renderer|string
 */
function view($template = null, $data = null)
{
    $renderer = app('renderer');

    if (is_null($template)) {
        return $renderer;
    }

    return $renderer->render($template, $data);
}
