<?php

declare(strict_types=1);

namespace Engelsystem\Migrations;

use Engelsystem\Database\Migration\Migration;
use Illuminate\Database\Connection;
use Illuminate\Database\Schema\Builder as SchemaBuilder;

class RefactorPermissionsAndGroups extends Migration
{
    protected int $developer = 90;
    protected int $bureaucrat = 80;
    protected int $shiCo = 60;
    protected int $newsAdmin = 85;
    protected int $teamCoordinator = 65;
    protected int $angel = 20;

    protected int $active;
    protected int $driveEdit;
    protected int $eventConfig;
    protected int $goodieEdit;
    protected int $ifsgEdit;
    protected int $log;
    protected int $news;
    protected int $register;
    protected int $scheduleImport;
    protected int $shifts;
    protected int $user;
    protected int $userAngeltypes;
    protected int $userShifts;

    protected string $shiftentry = 'shiftentry_edit_angeltype_supporter';
    protected string $language = 'admin_language';
    protected string $userEdit = 'user.edit';
    protected string $userNickEdit = 'user.nick.edit';
    protected string $shifttypes = 'shifttypes';
    protected string $shifttypesView = 'shifttypes.view';

    protected Connection $db;

    public function __construct(SchemaBuilder $schema)
    {
        parent::__construct($schema);
        $this->db = $this->schema->getConnection();

        $this->active = $this->getPrivilegeId('admin_active');
        $this->driveEdit = $this->getPrivilegeId('user.drive.edit');
        $this->eventConfig = $this->getPrivilegeId('admin_event_config');
        $this->goodieEdit = $this->getPrivilegeId('user.goodie.edit');
        $this->ifsgEdit = $this->getPrivilegeId('user.ifsg.edit');
        $this->log = $this->getPrivilegeId('admin_log');
        $this->news = $this->getPrivilegeId('admin_news');
        $this->register = $this->getPrivilegeId('register');
        $this->scheduleImport = $this->getPrivilegeId('schedule.import');
        $this->shifts = $this->getPrivilegeId('admin_shifts');
        $this->user = $this->getPrivilegeId('admin_user');
        $this->userAngeltypes = $this->getPrivilegeId('admin_user_angeltypes');
        $this->userShifts = $this->getPrivilegeId('user_shifts_admin');
    }

    /**
     * Run the migration
     */
    public function up(): void
    {
        $this->deletePermission($this->shiftentry);
        $this->deletePermission($this->language);

        $this->movePermission($this->active, $this->bureaucrat, $this->shiCo);
        $this->movePermission($this->userAngeltypes, $this->bureaucrat, $this->shiCo);
        $this->movePermission($this->eventConfig, $this->shiCo, $this->developer);
        $this->movePermission($this->goodieEdit, $this->bureaucrat, $this->shiCo);

        $this->insertGroupPermission($this->log, $this->bureaucrat);

        $this->deleteGroupPermission($this->news, $this->bureaucrat);
        $this->deleteGroupPermission($this->shifts, $this->bureaucrat);
        $this->deleteGroupPermission($this->user, $this->bureaucrat);
        $this->deleteGroupPermission($this->register, $this->bureaucrat);
        $this->deleteGroupPermission($this->scheduleImport, $this->developer);

        $this->updatePermission($this->shifttypes, $this->shifttypesView, 'View shift types');
        $this->updatePermission($this->userEdit, $this->userNickEdit, 'Edit user nick');

        $this->deleteGroup($this->newsAdmin);
        $this->deleteGroup($this->teamCoordinator);
    }

    /**
     * Reverse the migration
     */
    public function down(): void
    {
        $this->insertPermission(
            $this->shiftentry,
            'If user with this privilege is angeltype supporter, he can put users in shifts for their angeltype',
            $this->angel
        );
        $this->insertPermission(
            $this->language,
            'Translate the system',
            $this->developer
        );

        $this->movePermission($this->active, $this->shiCo, $this->bureaucrat);
        $this->movePermission($this->userAngeltypes, $this->shiCo, $this->bureaucrat);
        $this->movePermission($this->eventConfig, $this->developer, $this->shiCo);
        $this->movePermission($this->goodieEdit, $this->shiCo, $this->bureaucrat);

        $this->deleteGroupPermission($this->log, $this->bureaucrat);

        $this->insertGroupPermission($this->news, $this->bureaucrat);
        $this->insertGroupPermission($this->shifts, $this->bureaucrat);
        $this->insertGroupPermission($this->user, $this->bureaucrat);
        $this->insertGroupPermission($this->register, $this->bureaucrat);
        $this->insertGroupPermission($this->scheduleImport, $this->developer);

        $this->updatePermission($this->shifttypesView, $this->shifttypes, 'Administrate shift types');
        $this->updatePermission($this->userNickEdit, $this->userEdit, 'Edit user');

        $this->insertGroup($this->newsAdmin, 'News Admin', [$this->news]);
        $this->insertGroup($this->teamCoordinator, 'Team Coordinator', [
                $this->news,
                $this->userAngeltypes,
                $this->driveEdit,
                $this->ifsgEdit,
                $this->userShifts,
            ]);
    }

    protected function getPrivilegeId(string $privilege): int
    {
        return $this->db->table('privileges')
            ->where('name', $privilege)
            ->get(['id'])
            ->first()->id;
    }

    protected function deleteGroup(int $group): void
    {
        $this->db->table('groups')
            ->where(['id' => $group])
            ->delete();
    }

    protected function insertGroup(int $id, string $name, array $privileges): void
    {
        $this->db->table('groups')
            ->insertOrIgnore([
                'name' => $name,
                'id' => $id,
            ]);
        foreach ($privileges as $privilege) {
            $this->insertGroupPermission($privilege, $id);
        }
    }

    protected function deleteGroupPermission(int $privilege, int $group): void
    {
        $this->db->table('group_privileges')
            ->where(['group_id' => $group, 'privilege_id' => $privilege])
            ->delete();
    }

    protected function insertGroupPermission(int $privilege, int $group): void
    {
        $this->db->table('group_privileges')
            ->insertOrIgnore([
                ['group_id' => $group, 'privilege_id' => $privilege],
            ]);
    }

    protected function movePermission(int $privilege, int $oldGroup, int $newGroup): void
    {
        $this->insertGroupPermission($privilege, $newGroup);
        $this->deleteGroupPermission($privilege, $oldGroup);
    }

    protected function insertPermission(string $name, string $description, int $group): void
    {
        $this->db->table('privileges')
            ->insertOrIgnore([
                'name' => $name, 'description' => $description,
            ]);
        $permission = $this->getPrivilegeId($name);
        $this->insertGroupPermission($permission, $group);
    }

    protected function deletePermission(string $privilege): void
    {
        $this->db->table('privileges')
            ->where(['name' => $privilege])
            ->delete();
    }

    protected function updatePermission(string $oldName, string $newName, string $description): void
    {
        $this->db->table('privileges')->where('name', $oldName)->update([
            'name' => $newName,
            'description' => $description,
        ]);
    }
}
