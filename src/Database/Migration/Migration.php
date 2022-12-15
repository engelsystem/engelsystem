<?php

namespace Engelsystem\Database\Migration;

use Illuminate\Database\Schema\Builder as SchemaBuilder;

abstract class Migration
{
    protected SchemaBuilder $schema;

    /**
     * Migration constructor.
     *
     */
    public function __construct(SchemaBuilder $schemaBuilder)
    {
        $this->schema = $schemaBuilder;
    }
}
