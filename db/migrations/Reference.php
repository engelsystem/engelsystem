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
     */
    protected function referencesUser(Blueprint $table, bool $setPrimary = false)
    {
        $this->references($table, 'users', null, $setPrimary);
    }

    /**
     * @param Blueprint   $table
     * @param string      $targetTable
     * @param string|null $fromColumn
     * @param bool        $setPrimary
     * @return ColumnDefinition
     */
    protected function references(
        Blueprint $table,
        string $targetTable,
        ?string $fromColumn = null,
        bool $setPrimary = false
    ): ColumnDefinition {
        $fromColumn = $fromColumn ?? Str::singular($targetTable) . '_id';
        $col = $table->unsignedInteger($fromColumn);

        if ($setPrimary) {
            $table->primary($fromColumn);
        }

        $table->foreign($fromColumn)
            ->references('id')->on($targetTable)
            ->onUpdate('cascade')
            ->onDelete('cascade');

        return $col;
    }
}
