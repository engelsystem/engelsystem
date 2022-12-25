<?php

declare(strict_types=1);

namespace Engelsystem\Migrations;

use Engelsystem\Database\Migration\Migration;
use Illuminate\Database\Schema\Blueprint;
use stdClass;

class CreateUserAngelTypesTable extends Migration
{
    use ChangesReferences;
    use Reference;

    /**
     * Creates the new table, copies the data and drops the old one
     */
    public function up(): void
    {
        $connection = $this->schema->getConnection();

        $this->schema->create('user_angel_type', function (Blueprint $table): void {
            $table->increments('id');
            $this->referencesUser($table);
            $this->references($table, 'angel_types')->index();
            $this->references($table, 'users', 'confirm_user_id')->nullable()->default(null)->index();
            $table->boolean('supporter')->default(false)->index();
            $table->index(['user_id', 'angel_type_id', 'confirm_user_id']);
            $table->unique(['user_id', 'angel_type_id']);
        });

        if (!$this->schema->hasTable('UserAngelTypes')) {
            return;
        }

        /** @var stdClass[] $records */
        $records = $connection
            ->table('UserAngelTypes')
            ->get();
        foreach ($records as $record) {
            $connection->table('user_angel_type')->insert([
                'id'              => $record->id,
                'user_id'         => $record->user_id,
                'angel_type_id'   => $record->angeltype_id,
                'confirm_user_id' => $record->confirm_user_id ?: null,
                'supporter'       => (bool) $record->supporter,
            ]);
        }

        $this->changeReferences(
            'UserAngelTypes',
            'id',
            'user_angel_type',
            'id'
        );

        $this->schema->drop('UserAngelTypes');
    }

    /**
     * Recreates the previous table, copies the data and drops the new one
     */
    public function down(): void
    {
        $connection = $this->schema->getConnection();

        $this->schema->create('UserAngelTypes', function (Blueprint $table): void {
            $table->increments('id');
            $this->referencesUser($table);
            $this->references($table, 'angel_types', 'angeltype_id')->index('angeltype_id');
            $this->references($table, 'users', 'confirm_user_id')->nullable()->index('confirm_user_id');
            $table->boolean('supporter')->nullable()->index('coordinator');
            $table->index(['user_id', 'angeltype_id', 'confirm_user_id'], 'user_id');
        });

        /** @var stdClass[] $records */
        $records = $connection
            ->table('user_angel_type')
            ->get();
        foreach ($records as $record) {
            $connection->table('UserAngelTypes')->insert([
                'id'              => $record->id,
                'user_id'         => $record->user_id,
                'angeltype_id'    => $record->angel_type_id,
                'confirm_user_id' => $record->confirm_user_id ?: null,
                'supporter'       => (bool) $record->supporter,
            ]);
        }

        $this->changeReferences(
            'user_angel_type',
            'id',
            'UserAngelTypes',
            'id',
            'integer'
        );

        $this->schema->drop('user_angel_type');
    }
}
