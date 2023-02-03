<?php

declare(strict_types=1);

namespace Engelsystem\Migrations;

use Engelsystem\Database\Migration\Migration;
use Illuminate\Database\Schema\Builder as SchemaBuilder;

class AnotherStuff extends Migration
{
    /**
     * Run the migration
     */
    public function up(): void
    {
        // nope
    }

    /**
     * Reverse the migration
     */
    public function down(): void
    {
        // nope
    }

    public function getSchema(): SchemaBuilder
    {
        return $this->schema;
    }
}
