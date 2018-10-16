<?php

namespace Engelsystem\Migrations;

use Illuminate\Database\Schema\Blueprint;
use stdClass;

trait ChangesReferences
{
    /**
     * @param string $fromTable
     * @param string $fromColumn
     * @param string $targetTable
     * @param string $targetColumn
     * @param string $type
     */
    protected function changeReferences($fromTable, $fromColumn, $targetTable, $targetColumn, $type)
    {
        $references = $this->getReferencingTables($fromTable, $fromColumn);

        foreach ($references as $reference) {
            /** @var stdClass $reference */
            $this->schema->table($reference->table, function (Blueprint $table) use ($reference) {
                $table->dropForeign($reference->constraint);
            });

            $this->schema->table($reference->table,
                function (Blueprint $table) use ($reference, $targetTable, $targetColumn, $type) {
                    $table->{$type}($reference->column)->change();

                    $table->foreign($reference->column)
                        ->references($targetColumn)->on($targetTable)
                        ->onDelete('cascade');
                });
        }
    }

    /**
     * @param string $table
     * @param string $column
     * @return array
     */
    protected function getReferencingTables($table, $column): array
    {
        return $this->schema
            ->getConnection()
            ->select(
                '
                    SELECT
                            `TABLE_NAME` as "table",
                            `COLUMN_NAME` as "column",
                            `CONSTRAINT_NAME` as "constraint"
                    FROM information_schema.KEY_COLUMN_USAGE
                    WHERE REFERENCED_TABLE_SCHEMA = ?
                    AND REFERENCED_TABLE_NAME = ?
                    AND REFERENCED_COLUMN_NAME = ?
                ',
                [
                    $this->schema->getConnection()->getDatabaseName(),
                    $table,
                    $column,
                ]
            );
    }
}
