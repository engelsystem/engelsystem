<?php

// Check for autoloader
if (!is_readable(__DIR__ . '/../vendor/autoload.php')) {
    echo 'Please run composer.phar install';
    exit(1);
}

// Include composer autoloader
$loader = require __DIR__ . '/../vendor/autoload.php';
