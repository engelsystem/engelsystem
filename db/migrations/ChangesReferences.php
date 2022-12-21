<?php

namespace Engelsystem\Migrations;

use Illuminate\Database\Schema\Blueprint;
use stdClass;

trait ChangesReferences
{
    protected function changeReferences(
        string $fromTable,
        string $fromColumn,
        string $targetTable,
        string $targetColumn,
        string $type = 'unsignedInteger'
    ): void {
        $references = $this->getReferencingTables($fromTable, $fromColumn);

        foreach ($references as $reference) {
            /** @var stdClass $reference */
            $this->schema->table($reference->table, function (Blueprint $table) use ($reference): void {
                $table->dropForeign($reference->constraint);
            });

            $this->schema->table(
                $reference->table,
                function (Blueprint $table) use ($reference, $targetTable, $targetColumn, $type): void {
                    $table->{$type}($reference->column)->change();

                    $table->foreign($reference->column)
                        ->references($targetColumn)->on($targetTable)
                        ->onUpdate('cascade')
                        ->onDelete('cascade');
                }
            );
        }
    }

    protected function getReferencingTables(string $table, string $column): array
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
