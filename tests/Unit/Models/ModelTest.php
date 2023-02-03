<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Models;

use Engelsystem\Test\Unit\HasDatabase;
use Engelsystem\Test\Unit\TestCase;

abstract class ModelTest extends TestCase
{
    use HasDatabase;

    /**
     * Prepare test
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->initDatabase();
    }
}
