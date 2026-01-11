<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Database\Migration;

use Engelsystem\Database\Migration\Migration;
use Engelsystem\Migrations\AnotherStuff;
use Illuminate\Database\Schema\Builder as SchemaBuilder;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;

#[CoversMethod(Migration::class, '__construct')]
class MigrationTest extends TestCase
{
    public function testConstructor(): void
    {
        require_once __DIR__ . '/Stub/2017_12_24_053300_another_stuff.php';

        $schemaBuilder = $this->getStubBuilder(SchemaBuilder::class)
            ->disableOriginalConstructor()
            ->getStub();

        $instance = new AnotherStuff($schemaBuilder);
        $this->assertEquals($schemaBuilder, $instance->getSchema());
    }
}
