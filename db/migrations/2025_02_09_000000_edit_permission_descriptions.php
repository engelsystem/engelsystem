<?php

declare(strict_types=1);

namespace Engelsystem\Migrations;

use Engelsystem\Database\Migration\Migration;
use Illuminate\Database\Connection;
use Illuminate\Database\Schema\Builder as SchemaBuilder;

class EditPermissionDescriptions extends Migration
{
    protected Connection $db;

    protected array $permissions;
    public function __construct(SchemaBuilder $schema)
    {
        parent::__construct($schema);
        $this->db = $this->schema->getConnection();

        // name, new description, old description
        $this->permissions = [
            ['admin_angel_types', 'Edit angel types', 'Engel Typen administrieren'],
            ['login', 'Login', 'Logindialog'],
            ['logout', 'Logout', 'User darf sich ausloggen'],
            ['news', 'View news', 'Anzeigen der News-Seite'],
            ['news.highlight', 'Highlight news', 'Highlight News'],
            ['register', 'Register users', 'Einen neuen Engel registerieren'],
            ['start', 'Start page', 'Startseite für Gäste/Nicht eingeloggte User'],
            ['user.drive.edit', 'Edit driving license', 'Edit Driving License'],
            ['user.fa.edit', 'Edit user force active state', 'Edit User Force Active State'],
            ['user.ifsg.edit', 'Edit IfSG certificate', 'Edit IfSG Certificate'],
            ['user.info.edit', 'Edit user info', 'Edit User Info'],
            ['user.info.show', 'View user info', 'Show User Info'],
        ];
    }

    /**
     * Run the migration
     */
    public function up(): void
    {
        foreach ($this->permissions as $permission) {
            $this->updatePrivilegeDescription($permission[0], $permission[1]);
        }
        $this->db->table('privileges')
            ->where('name', 'user.info.show')
            ->update(['name' => 'user.info.view']);
    }

    /**
     * Reverse the migration
     */
    public function down(): void
    {
        $this->db->table('privileges')
            ->where('name', 'user.info.view')
            ->update(['name' => 'user.info.show']);
        foreach ($this->permissions as $permission) {
            $this->updatePrivilegeDescription($permission[0], $permission[2]);
        }
    }

    private function updatePrivilegeDescription(string $privilege, string $description): void
    {
        $this->db->table('privileges')
            ->where('name', $privilege)
            ->update(['description' => $description]);
    }
}
