<?php

declare(strict_types=1);

namespace Engelsystem\Migrations;

use Engelsystem\Database\Migration\Migration;
use Illuminate\Database\Schema\Blueprint;
use stdClass;

class CreateShiftEntriesTable extends Migration
{
    use ChangesReferences;
    use Reference;

    /**
     * Creates the new table, copies the data and drops the old one
     */
    public function up(): void
    {
        $connection = $this->schema->getConnection();
        $previous = $this->schema->hasTable('ShiftEntry');

        $this->schema->create('shift_entries', function (Blueprint $table): void {
            $table->increments('id');
            $this->references($table, 'shifts');
            $this->references($table, 'angel_types');
            $this->referencesUser($table);

            $table->mediumText('user_comment')->default('');

            $table->boolean('freeloaded')->default(false)->index();
            $table->mediumText('freeloaded_comment')->default('');

            $table->index(['angel_type_id', 'shift_id']);
        });

        if (!$previous) {
            return;
        }

        /** @var stdClass[] $records */
        $records = $connection
            ->table('ShiftEntry')
            ->get();
        foreach ($records as $record) {
            $connection->table('shift_entries')->insert([
                'id'                 => $record->id,
                'shift_id'           => $record->SID,
                'angel_type_id'      => $record->TID,
                'user_id'            => $record->UID,
                'user_comment'       => $record->Comment,
                'freeloaded'         => (bool) $record->freeloaded,
                'freeloaded_comment' => $record->freeload_comment,
            ]);
        }

        $this->changeReferences(
            'ShiftEntry',
            'id',
            'shift_entries',
            'id'
        );

        $this->schema->drop('ShiftEntry');
    }

    /**
     * Recreates the previous table, copies the data and drops the new one
     */
    public function down(): void
    {
        $connection = $this->schema->getConnection();

        $this->schema->create('ShiftEntry', function (Blueprint $table): void {
            $table->increments('id');
            $this->references($table, 'shifts', 'SID')->default(0);
            $this->references($table, 'angel_types', 'TID')->default(0);
            $this->references($table, 'users', 'UID')->default(0);

            $table->mediumText('Comment')->nullable();

            $table->mediumText('freeload_comment')->nullable()->default(null);
            $table->boolean('freeloaded')->index();

            $table->index(['SID', 'TID']);
        });

        /** @var stdClass[] $records */
        $records = $connection
            ->table('shift_entries')
            ->get();
        foreach ($records as $record) {
            $connection->table('ShiftEntry')->insert([
                'id'               => $record->id,
                'SID'              => $record->shift_id,
                'TID'              => $record->angel_type_id,
                'UID'              => $record->user_id,
                'Comment'          => $record->user_comment,
                'freeloaded'       => (bool) $record->freeloaded,
                'freeload_comment' => $record->freeloaded_comment,
            ]);
        }

        $this->changeReferences(
            'shift_entries',
            'id',
            'ShiftEntry',
            'id'
        );

        $this->schema->drop('shift_entries');
    }
}
