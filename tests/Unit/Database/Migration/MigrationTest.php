<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Database\Migration;

use Engelsystem\Test\Unit\Database\Migration\Stub\AnotherStuff;
use Illuminate\Database\Schema\Builder as SchemaBuilder;
use PHPUnit\Framework\MockObject\MockBuilder;
use PHPUnit\Framework\TestCase;

class MigrationTest extends TestCase
{
    /**
     * @covers \Engelsystem\Database\Migration\Migration::__construct
     */
    public function testConstructor(): void
    {
        require_once __DIR__ . '/Stub/2017_12_24_053300_another_stuff.php';

        /** @var MockBuilder|SchemaBuilder $schemaBuilder */
        $schemaBuilder = $this->getMockBuilder(SchemaBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();

        $instance = new AnotherStuff($schemaBuilder);
        $this->assertEquals($schemaBuilder, $instance->getSchema());
    }
}
