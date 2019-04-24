<?php

namespace Engelsystem\Migrations;

use Engelsystem\Database\Migration\Migration;
use Illuminate\Database\Schema\Builder as SchemaBuilder;

class AnotherStuff extends Migration
{
    /**
     * Run the migration
     */
    public function up()
    {
        // nope
    }

    /**
     * Reverse the migration
     */
    public function down()
    {
        // nope
    }

    /**
     * @return SchemaBuilder
     */
    public function getSchema()
    {
        return $this->schema;
    }
}
