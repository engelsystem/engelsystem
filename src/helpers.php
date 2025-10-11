<?php

declare(strict_types=1);

use Engelsystem\Application;
use Engelsystem\Config\Config;
use Engelsystem\Events\EventDispatcher;
use Engelsystem\Helpers\Authenticator;
use Engelsystem\Helpers\Cache;
use Engelsystem\Helpers\Translation\Translator;
use Engelsystem\Http\Redirector;
use Engelsystem\Http\Request;
use Engelsystem\Http\Response;
use Engelsystem\Http\UrlGeneratorInterface;
use Engelsystem\Renderer\Renderer;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Get the global app instance
 * @return mixed|Application
 * @phpcsSuppress SlevomatCodingStandard.TypeHints.ReturnTypeHint.UselessAnnotation
 */
function app(?string $id = null): mixed
{
    if (is_null($id)) {
        return Application::getInstance();
    }

    return Application::getInstance()->get($id);
}

function auth(): Authenticator
{
    return app('authenticator');
}

function base_path(string $path = ''): string
{
    return app('path') . (empty($path) ? '' : DIRECTORY_SEPARATOR . $path);
}

function back(int $status = 302, array $headers = []): Response
{
    /** @var Redirector $redirect */
    $redirect = app('redirect');

    return $redirect->back($status, $headers);
}

function cache(string|null $key = null, mixed $default = null, int $seconds = 60 * 60): mixed
{
    /** @var Cache $cache */
    $cache = app('cache');

    if (empty($key)) {
        return $cache;
    }

    return $cache->get($key, $default, $seconds);
}

/**
 * Get or set config values
 * @return mixed|Config
 * @phpcsSuppress SlevomatCodingStandard.TypeHints.ReturnTypeHint.UselessAnnotation
 */
function config(string|array|null $key = null, mixed $default = null): mixed
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

function config_path(string $path = ''): string
{
    return app('path.config') . (empty($path) ? '' : DIRECTORY_SEPARATOR . $path);
}

/**
 * Get a password from an environment variable. If an environment variable
 * called `${var}_FILE` is set, read the password from that file. Otherwise
 * returns the content of the `$var` environment variable.
 */
function env_secret(string $var, mixed $default = null): string | null
{
    $filename = env($var . '_FILE');
    if ($filename && file_exists($filename)) {
        return file_get_contents($filename);
    }

    return env($var, $default);
}

function event(string|object|null $event = null, array $payload = []): array|EventDispatcher
{
    /** @var EventDispatcher $dispatcher */
    $dispatcher = app('events.dispatcher');

    if (!is_null($event)) {
        return $dispatcher->dispatch($event, $payload);
    }

    return $dispatcher;
}

function redirect(string $path, int $status = 302, array $headers = []): Response
{
    /** @var Redirector $redirect */
    $redirect = app('redirect');

    return $redirect->to($path, $status, $headers);
}

/**
 * @return mixed|Request
 * @phpcsSuppress SlevomatCodingStandard.TypeHints.ReturnTypeHint.UselessAnnotation
 */
function request(?string $key = null, mixed $default = null): mixed
{
    /** @var Request $request */
    $request = app('request');

    if (is_null($key)) {
        return $request;
    }

    return $request->input($key, $default);
}

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
 * @return mixed|SessionInterface
 * @phpcsSuppress SlevomatCodingStandard.TypeHints.ReturnTypeHint.UselessAnnotation
 */
function session(?string $key = null, mixed $default = null): mixed
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
 */
function trans(?string $key = null, array $replace = []): string|Translator
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
 */
function __(string $key, array $replace = []): string
{
    /** @var Translator $translator */
    $translator = app('translator');

    return $translator->translate($key, $replace);
}

/**
 * Translate the given message
 */
function _e(string $key, string $keyPlural, int $number, array $replace = []): string
{
    /** @var Translator $translator */
    $translator = app('translator');

    return $translator->translatePlural($key, $keyPlural, $number, $replace);
}

function url(?string $path = null, array $parameters = []): UrlGeneratorInterface|string
{
    /** @var UrlGeneratorInterface $urlGenerator */
    $urlGenerator = app('http.urlGenerator');

    if (is_null($path)) {
        return $urlGenerator;
    }

    return $urlGenerator->to($path, $parameters);
}

function view(?string $template = null, array $data = []): Renderer|string
{
    /** @var Renderer $renderer */
    $renderer = app('renderer');

    if (is_null($template)) {
        return $renderer;
    }

    return $renderer->render($template, $data);
}
