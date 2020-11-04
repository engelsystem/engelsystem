<?php

declare(strict_types=1);

namespace Engelsystem\Migrations;

use Carbon\Carbon;
use Engelsystem\Database\Migration\Migration;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Schema\Blueprint;
use stdClass;

/**
 * This migration creates the "messages" table and copies the existing "Messages" table records to the new one.
 */
class CreateMessagesTable extends Migration
{
    use ChangesReferences;
    use Reference;

    /**
     * Creates the "messages" table, copies the data and drops the "Message" table.
     */
    public function up(): void
    {
        $hasPreviousMessagesTable = $this->schema->hasTable('Messages');

        if ($hasPreviousMessagesTable) {
            // Rename because some SQL DBMS handle identifiers case insensitive
            $this->schema->rename('Messages', 'PreviousMessages');
        }

        $this->createNewMessagesTable();

        if ($hasPreviousMessagesTable) {
            $this->copyPreviousToNewMessagesTable();
            $this->changeReferences(
                'PreviousMessages',
                'ID',
                'messages',
                'id'
            );
            $this->schema->drop('PreviousMessages');
        }
    }

    /**
     * Recreates the previous "Messages" table, copies back the data and drops the new "messages" table.
     */
    public function down(): void
    {
        // Rename as some SQL DBMS handle identifiers case insensitive
        $this->schema->rename('messages', 'new_messages');

        $this->createPreviousMessagesTable();
        $this->copyNewToPreviousMessagesTable();
        $this->changeReferences(
            'new_messages',
            'id',
            'Messages',
            'ID'
        );

        $this->schema->drop('new_messages');
    }

    /**
     * @return void
     */
    private function createNewMessagesTable(): void
    {
        $this->schema->create(
            'messages',
            function (Blueprint $table) {
                $table->increments('id');
                $this->referencesUser($table);
                $this->references($table, 'users', 'receiver_id');
                $table->boolean('read')->default(0);
                $table->text('text');
                $table->timestamps();
            }
        );
    }

    /**
     * @return void
     */
    private function copyPreviousToNewMessagesTable(): void
    {
        $connection = $this->schema->getConnection();
        /** @var stdClass[] $previousMessageRecords */
        $previousMessageRecords = $connection
            ->table('PreviousMessages')
            ->get();

        foreach ($previousMessageRecords as $previousMessage) {
            $date = Carbon::createFromTimestamp($previousMessage->Datum);
            $connection->table('messages')->insert(
                [
                    'id'          => $previousMessage->id,
                    'user_id'     => $previousMessage->SUID,
                    'receiver_id' => $previousMessage->RUID,
                    'read'        => $previousMessage->isRead === 'N' ? 0 : 1,
                    'text'        => $previousMessage->Text,
                    'created_at'  => $date,
                    'updated_at'  => $date,
                ]
            );
        }
    }

    /**
     * @return void
     */
    private function createPreviousMessagesTable(): void
    {
        $this->schema->create(
            'Messages',
            function (Blueprint $table) {
                $table->increments('id');
                $table->integer('Datum');
                $this->references($table, 'users', 'SUID');
                $this->references($table, 'users', 'RUID');
                $table->char('isRead')
                    ->default('N');
                $table->text('Text');
            }
        );
    }

    /**
     * @return void
     */
    private function copyNewToPreviousMessagesTable(): void
    {
        $connection = $this->schema->getConnection();
        /** @var Collection|stdClass[] $messageRecords */
        $messageRecords = $connection
            ->table('new_messages')
            ->get();

        foreach ($messageRecords as $messageRecord) {
            $date = Carbon::createFromFormat('Y-m-d H:i:s', $messageRecord->created_at)
                ->getTimestamp();

            $connection->table('Messages')->insert(
                [
                    'id'     => $messageRecord->id,
                    'Datum'  => $date,
                    'SUID'   => $messageRecord->user_id,
                    'RUID'   => $messageRecord->receiver_id,
                    'isRead' => $messageRecord->read === 0 ? 'N' : 'Y',
                    'Text'   => $messageRecord->text,
                ]
            );
        }
    }
}
