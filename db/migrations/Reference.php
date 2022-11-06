<?php

namespace Engelsystem\Migrations;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\ColumnDefinition;
use Illuminate\Support\Str;

trait Reference
{
    /**
     * @param Blueprint $table
     * @param bool      $setPrimary
     * @return ColumnDefinition
     */
    protected function referencesUser(Blueprint $table, bool $setPrimary = false): ColumnDefinition
    {
        return $this->references($table, 'users', null, null, $setPrimary);
    }

    /**
     * @param Blueprint   $table
     * @param string      $targetTable
     * @param string|null $fromColumn
     * @param string|null $targetColumn
     * @param bool        $setPrimary
     * @param string      $type
     * @return ColumnDefinition
     */
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

    /**
     * @param Blueprint   $table
     * @param string      $fromColumn
     * @param string      $targetTable
     * @param string|null $targetColumn
     */
    protected function addReference(
        Blueprint $table,
        string $fromColumn,
        string $targetTable,
        ?string $targetColumn = null
    ) {
        $table->foreign($fromColumn)
            ->references($targetColumn ?: 'id')->on($targetTable)
            ->onUpdate('cascade')
            ->onDelete('cascade');
    }
}
