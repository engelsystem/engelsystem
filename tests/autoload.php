<?php

use Composer\Autoload\ClassLoader;

require_once __DIR__ . '/../includes/autoload.php';

/** @var ClassLoader $loader */
$loader->addPsr4('Engelsystem\\Test\\', __DIR__ . '/');
