<?php

declare(strict_types=1);

namespace Engelsystem\Migrations;

use Engelsystem\Database\Migration\Migration;
use Illuminate\Database\Schema\Blueprint;

class RenameRoomsToLocations extends Migration
{
    /**
     * Run the migration
     */
    public function up(): void
    {
        $this->schema->rename('rooms', 'locations');

        $this->schema->table('shifts', function (Blueprint $table): void {
            $table->renameColumn('room_id', 'location_id');
        });

        $this->schema->table('needed_angel_types', function (Blueprint $table): void {
            $table->renameColumn('room_id', 'location_id');
        });

        $db = $this->schema->getConnection();
        $db->table('privileges')->where('name', 'admin_rooms')->update([
            'name' => 'admin_locations',
            'description' => 'Manage locations',
        ]);
        $db->table('privileges')->where('name', 'view_rooms')->update([
            'name' => 'view_locations',
            'description' => 'User can view locations',
        ]);
        $db->table('privileges')->where('name', 'schedule.import')->update([
            'description' => 'Import locations and shifts from schedule.xml',
        ]);
    }

    /**
     * Reverse the migration
     */
    public function down(): void
    {
        $this->schema->rename('locations', 'rooms');

        $this->schema->table('shifts', function (Blueprint $table): void {
            $table->renameColumn('location_id', 'room_id');
        });

        $this->schema->table('needed_angel_types', function (Blueprint $table): void {
            $table->renameColumn('location_id', 'room_id');
        });

        $db = $this->schema->getConnection();
        $db->table('privileges')->where('name', 'admin_locations')->update([
            'name' => 'admin_rooms',
            'description' => 'Räume administrieren',
        ]);
        $db->table('privileges')->where('name', 'view_locations')->update([
            'name' => 'view_rooms',
            'description' => 'User can view rooms',
        ]);
        $db->table('privileges')->where('name', 'schedule.import')->update([
            'description' => 'Import rooms and shifts from schedule.xml',
        ]);
    }
}
