<?php

declare(strict_types=1);

namespace Engelsystem\Migrations;

use Engelsystem\Database\Migration\Migration;
use Engelsystem\Models\Room;
use Illuminate\Database\Schema\Blueprint;
use stdClass;

class CreateRoomsTable extends Migration
{
    use ChangesReferences;
    use Reference;

    /**
     * Run the migration
     */
    public function up(): void
    {
        $this->schema->create('rooms', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 35)->unique();
            $table->string('map_url', 300)->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        if ($this->schema->hasTable('Room')) {
            $connection = $this->schema->getConnection();
            /** @var stdClass[] $previousRecords */
            $previousRecords = $connection
                ->table('Room')
                ->get();

            foreach ($previousRecords as $previousRecord) {
                $room = new Room([
                    'name'        => $previousRecord->Name,
                    'map_url'     => $previousRecord->map_url ?: null,
                    'description' => $previousRecord->description ?: null,
                ]);
                $room->setAttribute('id', $previousRecord->RID);
                $room->save();
            }

            $this->changeReferences(
                'Room',
                'RID',
                'rooms',
                'id'
            );

            $this->schema->drop('Room');
        }
    }

    /**
     * Reverse the migration
     */
    public function down(): void
    {
        $this->schema->create('Room', function (Blueprint $table) {
            $table->increments('RID');
            $table->string('Name', 35)->unique();
            $table->string('map_url', 300)->nullable();
            $table->mediumText('description')->nullable();
        });

        foreach (Room::all() as $room) {
            $this->schema
                ->getConnection()
                ->table('Room')
                ->insert([
                    'RID'         => $room->id,
                    'Name'        => $room->name,
                    'map_url'     => $room->map_url ?: null,
                    'description' => $room->description ?: null,
                ]);
        }

        $this->changeReferences(
            'rooms',
            'id',
            'Room',
            'RID'
        );

        $this->schema->drop('rooms');
    }
}
