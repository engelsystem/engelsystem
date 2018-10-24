<?php

namespace Engelsystem\Test\Unit\Database;

use Engelsystem\Migrations\AnotherStuff;
use Illuminate\Database\Schema\Builder as SchemaBuilder;
use PHPUnit\Framework\MockObject\MockBuilder;
use PHPUnit\Framework\TestCase;

class MigrationTest extends TestCase
{
    public function testConstructor()
    {
        require_once __DIR__ . '/Stub/2017_12_24_053300_another_stuff.php';

        /** @var MockBuilder|SchemaBuilder $schemaBuilder */
        $schemaBuilder = $this->getMockBuilder(SchemaBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();

        $instance = new AnotherStuff($schemaBuilder);
        $this->assertAttributeEquals($schemaBuilder, 'schema', $instance);
    }
}
