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
 * @return mixed|Application
 */
function app(string $id = null)
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
 * @return string
 */
function base_path(string $path = ''): string
{
    return app('path') . (empty($path) ? '' : DIRECTORY_SEPARATOR . $path);
}

/**
 * @param array $headers
 * @return Response
 */
function back(int $status = 302, array $headers = []): Response
{
    /** @var Redirector $redirect */
    $redirect = app('redirect');

    return $redirect->back($status, $headers);
}

/**
 * Get or set config values
 *
 * @return mixed|Config
 */
function config(string|array $key = null, mixed $default = null)
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
 * @return string
 */
function config_path(string $path = ''): string
{
    return app('path.config') . (empty($path) ? '' : DIRECTORY_SEPARATOR . $path);
}

/**
 * @param array              $payload
 *
 * @return EventDispatcher
 */
function event(string|object|null $event = null, array $payload = [])
{
    /** @var EventDispatcher $dispatcher */
    $dispatcher = app('events.dispatcher');

    if (!is_null($event)) {
        return $dispatcher->dispatch($event, $payload);
    }

    return $dispatcher;
}

/**
 * @param array  $headers
 * @return Response
 */
function redirect(string $path, int $status = 302, array $headers = []): Response
{
    /** @var Redirector $redirect */
    $redirect = app('redirect');

    return $redirect->to($path, $status, $headers);
}

/**
 * @return Request|mixed
 */
function request(string $key = null, mixed $default = null)
{
    /** @var Request $request */
    $request = app('request');

    if (is_null($key)) {
        return $request;
    }

    return $request->input($key, $default);
}

/**
 * @param array $headers
 * @return Response
 */
function response(mixed $content = '', int $status = 200, array $headers = []): Response
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
 * @return SessionInterface|mixed
 */
function session(string $key = null, mixed $default = null)
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
 * @param array  $replace
 * @return string|Translator
 */
function trans(string $key = null, array $replace = [])
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
 * @param array  $replace
 * @return string
 */
function __(string $key, array $replace = []): string
{
    /** @var Translator $translator */
    $translator = app('translator');

    return $translator->translate($key, $replace);
}

/**
 * Translate the given message
 *
 * @param array  $replace
 * @return string
 */
function _e(string $key, string $keyPlural, int $number, array $replace = []): string
{
    /** @var Translator $translator */
    $translator = app('translator');

    return $translator->translatePlural($key, $keyPlural, $number, $replace);
}

/**
 * @param array  $parameters
 * @return UrlGeneratorInterface|string
 */
function url(string $path = null, array $parameters = [])
{
    /** @var UrlGeneratorInterface $urlGenerator */
    $urlGenerator = app('http.urlGenerator');

    if (is_null($path)) {
        return $urlGenerator;
    }

    return $urlGenerator->to($path, $parameters);
}

/**
 * @param mixed[] $data
 * @return Renderer|string
 */
function view(string $template = null, array $data = [])
{
    /** @var Renderer $renderer */
    $renderer = app('renderer');

    if (is_null($template)) {
        return $renderer;
    }

    return $renderer->render($template, $data);
}
