<?php

use Engelsystem\Application;
use Engelsystem\Middleware\Dispatcher;
use Engelsystem\Middleware\ExceptionHandler;
use Engelsystem\Middleware\LegacyMiddleware;
use Engelsystem\Middleware\NotFoundResponse;
use Engelsystem\Middleware\SendResponseHandler;
use Psr\Http\Message\ServerRequestInterface;

require_once realpath(__DIR__ . '/../includes/engelsystem.php');

/** @var Application $app */
$app = app();

/** @var ServerRequestInterface $request */
$request = $app->get('psr7.request');

$dispatcher = new Dispatcher([
    SendResponseHandler::class,
    ExceptionHandler::class,
    LegacyMiddleware::class,
    NotFoundResponse::class,
]);
$dispatcher->setContainer($app);

$dispatcher->handle($request);
