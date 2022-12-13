<?php

namespace Engelsystem\Test\Feature;

use PHPUnit\Framework\TestCase;

abstract class ApplicationFeatureTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        $_SERVER['HTTP_HOST'] = 'foo.bar';
        require __DIR__ . '/../../includes/engelsystem.php';
    }

    /**
     * Undo the changes done by the ConfigureEnvironmentServiceProvider
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        ini_set('display_errors', true);
        error_reporting(E_ALL);

        ini_set('date.timezone', 'UTC');
        date_default_timezone_set('UTC');
    }
}
