<?php

declare(strict_types=1);

namespace Engelsystem\Migrations;

use Engelsystem\Database\Migration\Migration;
use Illuminate\Database\Schema\Blueprint;

class ShifttypeRemoveAngeltype extends Migration
{
    use Reference;

    /**
     * Run the migration
     */
    public function up(): void
    {
        if (!$this->schema->hasTable('ShiftTypes')) {
            return;
        }

        $this->schema->table('ShiftTypes', function (Blueprint $table): void {
            $table->dropForeign('shifttypes_ibfk_1');
            $table->dropColumn('angeltype_id');
        });
    }

    /**
     * Reverse the migration
     */
    public function down(): void
    {
        if (!$this->schema->hasTable('ShiftTypes')) {
            return;
        }

        $this->schema->table('ShiftTypes', function (Blueprint $table): void {
            $table->integer('angeltype_id')->after('name')->index()->nullable();
            $this->addReference($table, 'angeltype_id', 'AngelTypes', null, 'shifttypes_ibfk_1');
        });
    }
}
