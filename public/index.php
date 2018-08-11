<?php

use Engelsystem\Application;
use Engelsystem\Middleware\Dispatcher;
use Psr\Http\Message\ServerRequestInterface;

require_once realpath(__DIR__ . '/../includes/engelsystem.php');

/** @var Application $app */
$app = app();

/** @var ServerRequestInterface $request */
$request = $app->get('psr7.request');
$middleware = $app->getMiddleware();

$dispatcher = new Dispatcher($middleware);
$dispatcher->setContainer($app);

$dispatcher->handle($request);
