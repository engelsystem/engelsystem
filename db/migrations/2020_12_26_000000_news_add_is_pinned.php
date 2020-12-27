<?php

namespace Engelsystem\Migrations;

use Engelsystem\Database\Migration\Migration;
use Illuminate\Database\Schema\Blueprint;

class NewsAddIsPinned extends Migration
{
    use Reference;

    /**
     * Run the migration
     */
    public function up()
    {
        $this->schema->table(
            'news',
            function (Blueprint $table) {
                $table->boolean('is_pinned')->default(false)->after('is_meeting');
            }
        );
    }

    /**
     * Reverse the migration
     */
    public function down()
    {
        $this->schema->table(
            'news',
            function (Blueprint $table) {
                $table->dropColumn('is_pinned');
            }
        );
    }
}
