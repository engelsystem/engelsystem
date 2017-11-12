<?php

// Check for autoloader
if (!is_readable(__DIR__ . '/../vendor/autoload.php')) {
    die('Please run composer.phar install');
}

// Include composer autoloader
$loader = require __DIR__ . '/../vendor/autoload.php';
