<?php

use FastRoute\RouteCollector;
use Psr\Http\Message\ServerRequestInterface;

/** @var RouteCollector $route */

/** Demo route endpoint, TODO: Remove */
$route->addRoute('GET', '/hello/{name}', function ($request) {
    /** @var ServerRequestInterface $request */
    $name = $request->getAttribute('name');

    return response(sprintf('Hello %s!', htmlspecialchars($name)));
});
