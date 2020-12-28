<?php

use Engelsystem\Application;
use Engelsystem\Config\Config;
use Engelsystem\Events\EventDispatcher;
use Engelsystem\Helpers\Authenticator;
use Engelsystem\Helpers\Translation\Translator;
use Engelsystem\Http\Redirector;
use Engelsystem\Http\Request;
use Engelsystem\Http\Response;
use Engelsystem\Http\UrlGeneratorInterface;
use Engelsystem\Renderer\Renderer;
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
 * @return Authenticator
 */
function auth(): Authenticator
{
    return app('authenticator');
}

/**
 * @param string $path
 * @return string
 */
function base_path($path = ''): string
{
    return app('path') . (empty($path) ? '' : DIRECTORY_SEPARATOR . $path);
}

/**
 * @param int   $status
 * @param array $headers
 * @return Response
 */
function back($status = 302, $headers = []): Response
{
    /** @var Redirector $redirect */
    $redirect = app('redirect');

    return $redirect->back($status, $headers);
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
    /** @var Config $config */
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
function config_path($path = ''): string
{
    return app('path.config') . (empty($path) ? '' : DIRECTORY_SEPARATOR . $path);
}

/**
 * @param string|object|null $event
 * @param array              $payload
 *
 * @return EventDispatcher
 */
function event($event = null, $payload = [])
{
    /** @var EventDispatcher $dispatcher */
    $dispatcher = app('events.dispatcher');

    if (!is_null($event)) {
        return $dispatcher->dispatch($event, $payload);
    }

    return $dispatcher;
}

/**
 * @param string $path
 * @param int    $status
 * @param array  $headers
 * @return Response
 */
function redirect(string $path, $status = 302, $headers = []): Response
{
    /** @var Redirector $redirect */
    $redirect = app('redirect');

    return $redirect->to($path, $status, $headers);
}

/**
 * @param string $key
 * @param mixed  $default
 * @return Request|mixed
 */
function request($key = null, $default = null)
{
    /** @var Request $request */
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
function response($content = '', $status = 200, $headers = []): Response
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
    /** @var SessionInterface $session */
    $session = app('session');

    if (is_null($key)) {
        return $session;
    }

    return $session->get($key, $default);
}

/**
 * Translate the given message
 *
 * @param string $key
 * @param array  $replace
 * @return string|Translator
 */
function trans($key = null, $replace = [])
{
    /** @var Translator $translator */
    $translator = app('translator');

    if (is_null($key)) {
        return $translator;
    }

    return $translator->translate($key, $replace);
}

/**
 * Translate the given message
 *
 * @param string $key
 * @param array  $replace
 * @return string
 */
function __($key, $replace = []): string
{
    /** @var Translator $translator */
    $translator = app('translator');

    return $translator->translate($key, $replace);
}

/**
 * Translate the given message
 *
 * @param string $key
 * @param string $keyPlural
 * @param int    $number
 * @param array  $replace
 * @return string
 */
function _e($key, $keyPlural, $number, $replace = []): string
{
    /** @var Translator $translator */
    $translator = app('translator');

    return $translator->translatePlural($key, $keyPlural, $number, $replace);
}

/**
 * @param string $path
 * @param array  $parameters
 * @return UrlGeneratorInterface|string
 */
function url($path = null, $parameters = [])
{
    /** @var UrlGeneratorInterface $urlGenerator */
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
function view($template = null, $data = [])
{
    /** @var Renderer $renderer */
    $renderer = app('renderer');

    if (is_null($template)) {
        return $renderer;
    }

    return $renderer->render($template, $data);
}
