<?php

declare(strict_types=1);

namespace Engelsystem\Migrations;

use Engelsystem\Database\Migration\Migration;
use Engelsystem\Helpers\Carbon;
use Illuminate\Database\Schema\Blueprint;
use stdClass;

class CreateShiftsTable extends Migration
{
    use ChangesReferences;
    use Reference;

    /**
     * Creates the new table, copies the data and drops the old one
     */
    public function up(): void
    {
        $connection = $this->schema->getConnection();

        $previous = $this->schema->hasTable('Shifts');
        if ($previous) {
            $this->schema->rename('Shifts', 'shifts_old');
        }

        $this->schema->create('shifts', function (Blueprint $table): void {
            $table->increments('id');
            $table->string('title');
            $table->text('description')->default('');
            $table->string('url')->default('');
            $table->dateTime('start')->index();
            $table->dateTime('end');

            $this->references($table, 'shift_types');
            $this->references($table, 'rooms');

            $table->uuid('transaction_id')->nullable()->default(null)->index();

            $this->references($table, 'users', 'created_by');
            $this->references($table, 'users', 'updated_by')->nullable()->default(null);

            $table->timestamps();
        });

        if (!$previous) {
            return;
        }

        /** @var stdClass[] $records */
        $records = $connection
            ->table('shifts_old')
            ->get();
        foreach ($records as $record) {
            $isUpdated = !empty($record->edited_at_timestamp)
                && $record->edited_at_timestamp != $record->created_at_timestamp;

            $connection->table('shifts')->insert([
                'id'             => $record->SID,
                'title'          => (string) $record->title,
                'description'    => (string) $record->description,
                'url'            => (string) $record->URL,
                'start'          => Carbon::createFromTimestamp($record->start),
                'end'            => Carbon::createFromTimestamp($record->end),
                'shift_type_id'  => $record->shifttype_id,
                'room_id'        => $record->RID,
                'transaction_id' => $record->transaction_id,
                'created_by'     => $record->created_by_user_id,
                'updated_by'     => $isUpdated ? $record->edited_by_user_id : null,
                'created_at'     => Carbon::createFromTimestamp($record->created_at_timestamp),
                'updated_at'     => $isUpdated ? Carbon::createFromTimestamp($record->edited_at_timestamp) : null,
            ]);
        }

        $this->changeReferences(
            'shifts_old',
            'SID',
            'shifts',
            'id'
        );

        $this->schema->drop('shifts_old');
    }

    /**
     * Recreates the previous table, copies the data and drops the new one
     */
    public function down(): void
    {
        $connection = $this->schema->getConnection();

        $this->schema->rename('shifts', 'shifts_old');

        $this->schema->create('Shifts', function (Blueprint $table): void {
            $table->increments('SID');
            $table->mediumText('title')->nullable()->default(null);
            $this->references($table, 'shift_types', 'shifttype_id');
            $table->text('description')->nullable()->default(null);
            $table->integer('start')->index();
            $table->integer('end');
            $this->references($table, 'rooms', 'RID')->default(0);
            $table->mediumText('URL')->nullable()->default(null);

            $this->references($table, 'users', 'created_by_user_id')->nullable()->default(null);
            $table->integer('created_at_timestamp');
            $this->references($table, 'users', 'edited_by_user_id')->nullable()->default(null);
            $table->integer('edited_at_timestamp');

            $table->uuid('transaction_id')->nullable()->default(null)->index();
        });

        /** @var stdClass[] $records */
        $records = $connection
            ->table('shifts_old')
            ->get();
        $now = Carbon::now()->getTimestamp();
        foreach ($records as $record) {
            $createdAt = $record->created_at ? Carbon::make($record->created_at)->getTimestamp() : $now;
            $updatedAt = $record->updated_at ? Carbon::make($record->updated_at)->getTimestamp() : $createdAt;

            $connection->table('Shifts')->insert([
                'SID'                  => $record->id,
                'title'                => $record->title,
                'shifttype_id'         => $record->shift_type_id,
                'description'          => $record->description ?: null,
                'start'                => Carbon::make($record->start)->getTimestamp(),
                'end'                  => Carbon::make($record->end)->getTimestamp(),
                'RID'                  => $record->room_id,
                'URL'                  => $record->url ?: null,
                'created_by_user_id'   => $record->created_by,
                'created_at_timestamp' => $createdAt,
                'edited_by_user_id'    => $record->updated_by,
                'edited_at_timestamp'  => $updatedAt,
                'transaction_id'       => $record->transaction_id,
            ]);
        }

        $this->changeReferences(
            'shifts_old',
            'id',
            'Shifts',
            'SID'
        );

        $this->schema->drop('shifts_old');
    }
}
