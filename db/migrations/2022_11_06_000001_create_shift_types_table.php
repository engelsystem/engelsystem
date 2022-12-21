<?php

declare(strict_types=1);

namespace Engelsystem\Migrations;

use Engelsystem\Database\Migration\Migration;
use Illuminate\Database\Schema\Blueprint;
use stdClass;

class CreateShiftTypesTable extends Migration
{
    use ChangesReferences;

    /**
     * Creates the new table, copies the data and drops the old one
     */
    public function up(): void
    {
        $connection = $this->schema->getConnection();
        $this->schema->create('shift_types', function (Blueprint $table): void {
            $table->increments('id');
            $table->string('name')->unique();
            $table->text('description');
        });

        if (!$this->schema->hasTable('ShiftTypes')) {
            return;
        }

        /** @var stdClass[] $records */
        $records = $connection
            ->table('ShiftTypes')
            ->get();
        foreach ($records as $record) {
            $connection->table('shift_types')->insert([
                'id'          => $record->id,
                'name'        => $record->name,
                'description' => $record->description,
            ]);
        }

        $this->changeReferences(
            'ShiftTypes',
            'id',
            'shift_types',
            'id'
        );

        $this->schema->drop('ShiftTypes');
    }

    /**
     * Recreates the previous table, copies the data and drops the new one
     */
    public function down(): void
    {
        $connection = $this->schema->getConnection();
        $this->schema->create('ShiftTypes', function (Blueprint $table): void {
            $table->increments('id');
            $table->string('name', 255);
            $table->mediumText('description');
        });

        /** @var stdClass[] $records */
        $records = $connection
            ->table('shift_types')
            ->get();
        foreach ($records as $record) {
            $connection->table('ShiftTypes')->insert([
                'id'          => $record->id,
                'name'        => $record->name,
                'description' => $record->description,
            ]);
        }

        $this->changeReferences(
            'shift_types',
            'id',
            'ShiftTypes',
            'id'
        );

        $this->schema->drop('shift_types');
    }
}
