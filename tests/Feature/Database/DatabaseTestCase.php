<?php

declare(strict_types=1);

namespace Engelsystem\Test\Feature\Database;

use Engelsystem\Test\Unit\TestCase;

abstract class DatabaseTestCase extends TestCase
{
    /**
     * Returns the database config
     *
     * @return string[]
     */
    protected function getDbConfig(): array
    {
        $configValues = ['database' => [
            'host' => env('MYSQL_HOST', 'localhost'),
            'database' => env('MYSQL_DATABASE', 'engelsystem'),
            'username' => env('MYSQL_USER', 'root'),
            'password' => env('MYSQL_PASSWORD', ''),
        ]];

        foreach ([__DIR__ . '/../../../config/config.php', __DIR__ . '/../../../config/config.local.php'] as $file) {
            if (file_exists($file)) {
                $configValues = array_replace_recursive($configValues, require $file);
            }
        }

        return $configValues;
    }
}
