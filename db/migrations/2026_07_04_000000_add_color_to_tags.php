<?php

declare(strict_types=1);

namespace Engelsystem\Migrations;

use Engelsystem\Database\Migration\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddColorToTags extends Migration
{
    use Reference;

    /**
     * Run the migration
     */
    public function up(): void
    {
        $this->schema->table('tags', function (Blueprint $table): void {
            $table->string('color')->after('name')->default('#424242');
        });

        $connection = $this->schema->getConnection();
        foreach ($connection->table('tags')->get() as $tag) {
            $color = '#' . str_pad(dechex(rand(0, 0xffffff)), 6, '0', STR_PAD_LEFT);
            $connection
                ->table('tags')
                ->where('id', $tag->id)
                ->update(['color' => $color]);
        }
    }

    /**
     * Reverse the migration
     */
    public function down(): void
    {
        $this->schema->table('tags', function (Blueprint $table): void {
            $table->dropColumn('color');
        });
    }
}
