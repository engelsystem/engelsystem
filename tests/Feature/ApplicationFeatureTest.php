<?php

namespace Engelsystem\Test\Feature;

use PHPUnit\Framework\TestCase;

abstract class ApplicationFeatureTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        $_SERVER['HTTP_HOST'] = 'foo.bar';
        require_once __DIR__ . '/../../includes/engelsystem.php';
    }
}
