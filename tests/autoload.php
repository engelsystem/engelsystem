<?php

use Composer\Autoload\ClassLoader;

require_once __DIR__ . '/../includes/autoload.php';

/** @var $loader ClassLoader */
$loader->addPsr4('Engelsystem\\Test\\', __DIR__ . '/');
