<?php

namespace Engelsystem\Migrations;

use Engelsystem\Database\Migration\Migration;
use Illuminate\Database\Schema\Blueprint;

class ChangeUsersContactDectFieldSize extends Migration
{
    /** @var array */
    protected $tables = [
        'AngelTypes'    => 'contact_dect',
        'users_contact' => 'dect',
    ];

    /**
     * Run the migration
     */
    public function up()
    {
        $this->changeDectTo(40);
    }

    /**
     * Reverse the migration
     */
    public function down()
    {
        $this->changeDectTo(5);
    }

    /**
     * @param int $length
     */
    protected function changeDectTo(int $length)
    {
        foreach ($this->tables as $table => $column) {
            if (!$this->schema->hasTable($table)) {
                continue;
            }

            $this->schema->table($table, function (Blueprint $table) use ($column, $length) {
                $table->string($column, $length)->change();
            });
        }
    }
}
