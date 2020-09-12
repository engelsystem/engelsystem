<?php

declare(strict_types=1);

namespace Engelsystem\Migrations;

use Carbon\Carbon;
use Engelsystem\Database\Migration\Migration;
use Engelsystem\Models\Worklog;
use Illuminate\Database\Schema\Blueprint;
use stdClass;

class CreateWorklogsTable extends Migration
{
    use ChangesReferences;
    use Reference;

    /**
     * Run the migration
     */
    public function up(): void
    {
        $this->schema->create('worklogs', function (Blueprint $table) {
            $table->increments('id');
            $this->referencesUser($table);
            $this->references($table, 'users', 'creator_id');
            $table->decimal('hours');
            $table->string('comment', 200);
            $table->date('worked_at');
            $table->timestamps();
        });

        if ($this->schema->hasTable('UserWorkLog')) {
            $connection = $this->schema->getConnection();
            /** @var stdClass[] $previousRecords */
            $previousRecords = $connection
                ->table('UserWorkLog')
                ->get();

            foreach ($previousRecords as $previousRecord) {
                $room = new Worklog([
                    'user_id'    => $previousRecord->user_id,
                    'creator_id' => $previousRecord->created_user_id,
                    'worked_at'  => $previousRecord->work_timestamp,
                    'hours'      => $previousRecord->work_hours,
                    'comment'    => $previousRecord->comment,
                ]);
                $created_at = Carbon::createFromTimestamp($previousRecord->created_timestamp);
                $room->setAttribute('id', $previousRecord->id);
                $room->setAttribute('created_at', $created_at);
                $room->setAttribute('updated_at', $created_at);
                $room->save();
            }

            $this->changeReferences(
                'UserWorkLog',
                'id',
                'worklogs',
                'id'
            );

            $this->schema->drop('UserWorkLog');
        }
    }

    /**
     * Reverse the migration
     */
    public function down(): void
    {
        $this->schema->create('UserWorkLog', function (Blueprint $table) {
            $table->increments('id');
            $this->referencesUser($table);
            $table->integer('work_timestamp');
            $table->decimal('work_hours');
            $table->string('comment', 200);
            $this->references($table, 'users', 'created_user_id');
            $table->integer('created_timestamp')->index();
        });

        foreach (Worklog::all() as $record) {
            /** @var Worklog $record */
            $this->schema
                ->getConnection()
                ->table('UserWorkLog')
                ->insert([
                    'id'                => $record->id,
                    'user_id'           => $record->user_id,
                    'work_timestamp'    => $record->worked_at->timestamp,
                    'work_hours'        => $record->hours,
                    'comment'           => $record->comment,
                    'created_user_id'   => $record->creator_id,
                    'created_timestamp' => $record->created_at->timestamp,
                ]);
        }

        $this->changeReferences(
            'worklogs',
            'id',
            'UserWorkLog',
            'id'
        );

        $this->schema->drop('worklogs');
    }
}
