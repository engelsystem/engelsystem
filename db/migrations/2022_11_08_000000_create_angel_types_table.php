<?php

declare(strict_types=1);

namespace Engelsystem\Migrations;

use Engelsystem\Database\Migration\Migration;
use Illuminate\Support\Collection;
use Illuminate\Database\Schema\Blueprint;
use stdClass;

class CreateAngelTypesTable extends Migration
{
    use ChangesReferences;

    /**
     * Creates the new table, copies the data and drops the old one
     */
    public function up(): void
    {
        $connection = $this->schema->getConnection();
        $this->schema->create('angel_types', function (Blueprint $table): void {
            $table->increments('id');
            $table->string('name')->unique();
            $table->text('description')->default('');

            $table->string('contact_name')->default('');
            $table->string('contact_dect')->default('');
            $table->string('contact_email')->default('');

            $table->boolean('restricted')->default(false);
            $table->boolean('requires_driver_license')->default(false);
            $table->boolean('no_self_signup')->default(false);
            $table->boolean('show_on_dashboard')->default(true);
            $table->boolean('hide_register')->default(false);
        });

        if (!$this->schema->hasTable('AngelTypes')) {
            return;
        }

        /** @var Collection|stdClass[] $records */
        $records = $connection
            ->table('AngelTypes')
            ->get();
        foreach ($records as $record) {
            $connection->table('angel_types')->insert([
                'id'          => $record->id,
                'name'        => $record->name,
                'description' => $record->description,

                'contact_name'  => (string) $record->contact_name,
                'contact_dect'  => (string) $record->contact_dect,
                'contact_email' => (string) $record->contact_email,

                'restricted'              => $record->restricted,
                'requires_driver_license' => $record->requires_driver_license,
                'no_self_signup'          => $record->no_self_signup,
                'show_on_dashboard'       => $record->show_on_dashboard,
                'hide_register'           => $record->hide_register,
            ]);
        }

        $this->changeReferences(
            'AngelTypes',
            'id',
            'angel_types',
            'id'
        );

        $this->schema->drop('AngelTypes');
    }

    /**
     * Recreates the previous table, copies the data and drops the new one
     */
    public function down(): void
    {
        $connection = $this->schema->getConnection();
        $this->schema->create('AngelTypes', function (Blueprint $table): void {
            $table->integer('id', true);
            $table->string('name', 50)->default('')->unique();
            $table->boolean('restricted');
            $table->mediumText('description');
            $table->boolean('requires_driver_license');
            $table->boolean('no_self_signup');
            $table->string('contact_name', 250)->nullable();
            $table->string('contact_dect', 40)->nullable();
            $table->string('contact_email', 250)->nullable();
            $table->boolean('show_on_dashboard');
            $table->boolean('hide_register')->default(false);
        });

        /** @var Collection|stdClass[] $records */
        $records = $connection
            ->table('angel_types')
            ->get();
        foreach ($records as $record) {
            $connection->table('AngelTypes')->insert([
                'id'          => $record->id,
                'name'        => $record->name,
                'description' => $record->description,

                'contact_name'  => $record->contact_name ?: null,
                'contact_dect'  => $record->contact_dect ?: null,
                'contact_email' => $record->contact_email ?: null,

                'restricted'              => $record->restricted,
                'requires_driver_license' => $record->requires_driver_license,
                'no_self_signup'          => $record->no_self_signup,
                'show_on_dashboard'       => $record->show_on_dashboard,
                'hide_register'           => $record->hide_register,
            ]);
        }

        $this->changeReferences(
            'angel_types',
            'id',
            'AngelTypes',
            'id',
            'integer'
        );

        $this->schema->drop('angel_types');
    }
}
