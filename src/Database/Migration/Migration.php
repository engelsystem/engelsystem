<?php

declare(strict_types=1);

namespace Engelsystem\Database\Migration;

use Illuminate\Database\Schema\Builder as SchemaBuilder;

abstract class Migration
{
    /**
     * Migration constructor.
     */
    public function __construct(protected SchemaBuilder $schema)
    {
    }
}
