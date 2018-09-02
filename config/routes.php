<?php

use FastRoute\RouteCollector;

/** @var RouteCollector $route */

$route->get('/credits', 'CreditsController@index');
