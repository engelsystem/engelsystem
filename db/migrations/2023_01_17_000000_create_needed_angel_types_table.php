<?php

declare(strict_types=1);

namespace Engelsystem\Migrations;

use Engelsystem\Database\Migration\Migration;
use Illuminate\Database\Schema\Blueprint;
use stdClass;

class CreateNeededAngelTypesTable extends Migration
{
    use ChangesReferences;
    use Reference;

    /**
     * Creates the new table, copies the data and drops the old one
     */
    public function up(): void
    {
        $connection = $this->schema->getConnection();
        $previous = $this->schema->hasTable('NeededAngelTypes');

        $this->schema->create('needed_angel_types', function (Blueprint $table): void {
            $table->increments('id');
            $this->references($table, 'rooms')->nullable();
            $this->references($table, 'shifts')->nullable();
            $this->references($table, 'angel_types');
            $table->integer('count')->index();

            $table->index(['room_id', 'angel_type_id']);
        });

        if (!$previous) {
            return;
        }

        // Delete old entries which don't need angels
        $connection
            ->table('NeededAngelTypes')
            ->where('count', 0)
            ->delete();

        /** @var stdClass[] $records */
        $records = $connection
            ->table('NeededAngelTypes')
            ->get();
        foreach ($records as $record) {
            $connection->table('needed_angel_types')->insert([
                'id'            => $record->id,
                'room_id'       => $record->room_id,
                'shift_id'      => $record->shift_id,
                'angel_type_id' => $record->angel_type_id,
                'count'         => $record->count,
            ]);
        }

        $this->changeReferences(
            'NeededAngelTypes',
            'id',
            'needed_angel_types',
            'id'
        );

        $this->schema->drop('NeededAngelTypes');
    }

    /**
     * Recreates the previous table, copies the data and drops the new one
     */
    public function down(): void
    {
        $connection = $this->schema->getConnection();

        $this->schema->create('NeededAngelTypes', function (Blueprint $table): void {
            $table->increments('id');
            $this->references($table, 'rooms')->nullable();
            $this->references($table, 'shifts')->nullable();
            $this->references($table, 'angel_types');
            $table->integer('count')->index();

            $table->index(['room_id', 'angel_type_id']);
        });

        /** @var stdClass[] $records */
        $records = $connection
            ->table('needed_angel_types')
            ->get();
        foreach ($records as $record) {
            $connection->table('NeededAngelTypes')->insert([
                'id'            => $record->id,
                'room_id'       => $record->room_id,
                'shift_id'      => $record->shift_id,
                'angel_type_id' => $record->angel_type_id,
                'count'         => $record->count,
            ]);
        }

        $this->changeReferences(
            'needed_angel_types',
            'id',
            'NeededAngelTypes',
            'id'
        );

        $this->schema->drop('needed_angel_types');
    }
}
