<?php

declare(strict_types=1);

namespace Engelsystem\Migrations;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\ColumnDefinition;
use Illuminate\Support\Str;

trait Reference
{
    protected function referencesUser(Blueprint $table, bool $setPrimary = false): ColumnDefinition
    {
        return $this->references($table, 'users', null, null, $setPrimary);
    }

    protected function references(
        Blueprint $table,
        string $targetTable,
        ?string $fromColumn = null,
        ?string $targetColumn = null,
        bool $setPrimary = false,
        string $type = 'unsignedInteger'
    ): ColumnDefinition {
        $fromColumn = $fromColumn ?? Str::singular($targetTable) . '_id';
        $col = $table->{$type}($fromColumn);

        if ($setPrimary) {
            $table->primary($fromColumn);
        }

        $this->addReference($table, $fromColumn, $targetTable, $targetColumn ?: 'id');

        return $col;
    }

    protected function addReference(
        Blueprint $table,
        string $fromColumn,
        string $targetTable,
        ?string $targetColumn = null,
        ?string $name = null
    ): void {
        $table->foreign($fromColumn, $name)
            ->references($targetColumn ?: 'id')->on($targetTable)
            ->onUpdate('cascade')
            ->onDelete('cascade');
    }
}
