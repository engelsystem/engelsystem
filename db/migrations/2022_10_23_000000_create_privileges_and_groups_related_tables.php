<?php

declare(strict_types=1);

namespace Engelsystem\Migrations;

use Engelsystem\Database\Migration\Migration;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Schema\Blueprint;
use stdClass;

class CreatePrivilegesAndGroupsRelatedTables extends Migration
{
    use ChangesReferences;
    use Reference;

    /**
     * Creates the new table, copies the data and drops the old one.
     */
    public function up(): void
    {
        $hasPrevious = $this->schema->hasTable('Privileges');

        if ($hasPrevious) {
            // Rename because some DBMS handle identifiers case-insensitive
            $this->schema->rename('Groups', 'groups_old');
            $this->schema->rename('Privileges', 'privileges_old');
        }

        $this->createNew();

        if ($hasPrevious) {
            $this->copyOldToNew();

            $this->changeReferences(
                'groups_old',
                'UID',
                'groups',
                'id'
            );
            $this->changeReferences(
                'privileges_old',
                'id',
                'privileges',
                'id'
            );
            $this->changeReferences(
                'UserGroups',
                'id',
                'users_groups',
                'id'
            );
            $this->changeReferences(
                'GroupPrivileges',
                'id',
                'group_privileges',
                'id'
            );

            $this->schema->drop('UserGroups');
            $this->schema->drop('GroupPrivileges');
            $this->schema->drop('groups_old');
            $this->schema->drop('privileges_old');
        }
    }

    /**
     * Recreates the previous table, copies the data and drops the new one.
     */
    public function down(): void
    {
        // Rename because some DBMS handle identifiers case-insensitive
        $this->schema->rename('groups', 'groups_new');
        $this->schema->rename('privileges', 'privileges_new');

        $this->createOldTable();
        $this->copyNewToOld();

        $this->changeReferences(
            'groups_new',
            'id',
            'Groups',
            'UID',
            'integer'
        );
        $this->changeReferences(
            'privileges_new',
            'id',
            'Privileges',
            'id'
        );
        $this->changeReferences(
            'users_groups',
            'id',
            'UserGroups',
            'id'
        );
        $this->changeReferences(
            'group_privileges',
            'id',
            'GroupPrivileges',
            'id'
        );

        $this->schema->drop('users_groups');
        $this->schema->drop('group_privileges');
        $this->schema->drop('groups_new');
        $this->schema->drop('privileges_new');
    }

    private function createNew(): void
    {
        $this->schema->create('groups', function (Blueprint $table): void {
            $table->increments('id');
            $table->string('name', 35)->unique();
        });

        $this->schema->create('privileges', function (Blueprint $table): void {
            $table->increments('id');
            $table->string('name', 128)->unique();
            $table->string('description', 1024);
        });

        $this->schema->create('users_groups', function (Blueprint $table): void {
            $table->increments('id');
            $this->referencesUser($table)->index();
            $this->references($table, 'groups')->index();
        });

        $this->schema->create('group_privileges', function (Blueprint $table): void {
            $table->increments('id');
            $this->references($table, 'groups')->index();
            $this->references($table, 'privileges')->index();
        });
    }

    private function createOldTable(): void
    {
        $this->schema->create('Groups', function (Blueprint $table): void {
            $table->string('Name', 35);
            $table->integer('UID')->primary();
        });

        $this->schema->create('Privileges', function (Blueprint $table): void {
            $table->increments('id');
            $table->string('name', 128)->unique();
            $table->string('desc', 1024);
        });

        $this->schema->create('UserGroups', function (Blueprint $table): void {
            $table->increments('id');
            $this->references($table, 'users', 'uid');
            $this->references($table, 'Groups', 'group_id', 'UID', false, 'integer')->index();
            $table->index(['uid', 'group_id']);
        });

        $this->schema->create('GroupPrivileges', function (Blueprint $table): void {
            $table->increments('id');
            $this->references($table, 'Groups', 'group_id', 'UID', false, 'integer');
            $this->references($table, 'Privileges', 'privilege_id')->index();
            $table->index(['group_id', 'privilege_id']);
        });
    }

    private function copyOldToNew(): void
    {
        $connection = $this->schema->getConnection();

        /** @var stdClass[] $records */
        $records = $connection
            ->table('groups_old')
            ->get();
        foreach ($records as $record) {
            $connection->table('groups')->insert([
                'id'   => $record->UID,
                'name' => $record->Name,
            ]);
        }

        $records = $connection
            ->table('privileges_old')
            ->get();
        foreach ($records as $record) {
            $connection->table('privileges')->insert([
                'id'          => $record->id,
                'name'        => $record->name,
                'description' => $record->desc,
            ]);
        }

        $records = $connection
            ->table('UserGroups')
            ->get();
        foreach ($records as $record) {
            $connection->table('users_groups')->insert([
                'id'       => $record->id,
                'user_id'  => $record->uid,
                'group_id' => $record->group_id,
            ]);
        }

        $records = $connection
            ->table('GroupPrivileges')
            ->get();
        foreach ($records as $record) {
            $connection->table('group_privileges')->insert([
                'id'           => $record->id,
                'group_id'     => $record->group_id,
                'privilege_id' => $record->privilege_id,
            ]);
        }
    }

    private function copyNewToOld(): void
    {
        $connection = $this->schema->getConnection();

        /** @var Collection|stdClass[] $records */
        $records = $connection
            ->table('groups_new')
            ->get();
        foreach ($records as $record) {
            $connection->table('Groups')->insert([
                'Name' => $record->name,
                'UID'  => $record->id,
            ]);
        }

        $records = $connection
            ->table('privileges_new')
            ->get();
        foreach ($records as $record) {
            $connection->table('Privileges')->insert([
                'id'   => $record->id,
                'name' => $record->name,
                'desc' => $record->description,
            ]);
        }

        $records = $connection
            ->table('users_groups')
            ->get();
        foreach ($records as $record) {
            $connection->table('UserGroups')->insert([
                'id'       => $record->id,
                'uid'      => $record->user_id,
                'group_id' => $record->group_id,
            ]);
        }

        $records = $connection
            ->table('group_privileges')
            ->get();
        foreach ($records as $record) {
            $connection->table('GroupPrivileges')->insert([
                'id'           => $record->id,
                'group_id'     => $record->group_id,
                'privilege_id' => $record->privilege_id,
            ]);
        }
    }
}
