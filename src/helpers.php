<?php
// Some useful functions

use Engelsystem\Application;
use Engelsystem\Config\Config;
use Engelsystem\Http\Request;
use Engelsystem\Http\Response;
use Engelsystem\Renderer\Renderer;
use Engelsystem\Http\UrlGenerator;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Get the global app instance
 *
 * @param string $id
 * @return mixed|Application
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
 * @param string $content
 * @param int    $status
 * @param array  $headers
 * @return Response
 */
function response($content = '', $status = 200, $headers = [])
{
    /** @var Response $response */
    $response = app('psr7.response');
    $response = $response
        ->withContent($content)
        ->withStatus($status);

    foreach ($headers as $key => $value) {
        $response = $response->withAddedHeader($key, $value);
    }

    return $response;
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
 * @return UrlGenerator|string
 */
function url($path = null, $parameters = [])
{
    $urlGenerator = app('http.urlGenerator');

    if (is_null($path)) {
        return $urlGenerator;
    }

    return $urlGenerator->to($path, $parameters);
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
