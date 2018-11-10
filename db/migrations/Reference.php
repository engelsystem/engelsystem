<?php

namespace Engelsystem\Migrations;

use Illuminate\Database\Schema\Blueprint;

trait Reference
{
    /**
     * @param Blueprint $table
     * @param bool      $setPrimary
     */
    protected function referencesUser(Blueprint $table, $setPrimary = true)
    {
        $this->references($table, 'users', 'user_id', $setPrimary);
    }

    /**
     * @param Blueprint $table
     * @param string    $targetTable
     * @param string    $fromColumn
     * @param bool      $setPrimary
     */
    protected function references(Blueprint $table, $targetTable, $fromColumn, $setPrimary = false)
    {
        $table->unsignedInteger($fromColumn);

        if ($setPrimary) {
            $table->primary($fromColumn);
        }

        $table->foreign($fromColumn)
            ->references('id')->on($targetTable)
            ->onDelete('cascade');
    }
}
