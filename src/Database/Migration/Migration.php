<?php

namespace Engelsystem\Database\Migration;

use Illuminate\Database\Schema\Builder as SchemaBuilder;

abstract class Migration
{
    /** @var SchemaBuilder */
    protected $schema;

    /**
     * Migration constructor.
     *
     * @param SchemaBuilder $schemaBuilder
     */
    public function __construct(SchemaBuilder $schemaBuilder)
    {
        $this->schema = $schemaBuilder;
    }
}
