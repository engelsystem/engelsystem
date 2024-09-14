<?php

declare(strict_types=1);

namespace Engelsystem\Migrations;

use Engelsystem\Database\Migration\Migration;
use Illuminate\Database\Connection;
use Illuminate\Database\Schema\Builder as SchemaBuilder;

class InternalStaffPermission extends Migration
{
    // ------------------------------------------------------------
    // Groups
    // ------------------------------------------------------------
    protected int $staffInternalID = 21;
    protected string $staffInternalText = 'Staff - Internal';

    protected int $shiftCoordinatorID = 60;
    protected int $bureaucratID = 80;
    protected int $developerID = 90;

    // ------------------------------------------------------------
    // Privileges
    // ------------------------------------------------------------
    protected int $userTypeInternalStaffID;
    protected string $userTypeInternalStaffText = 'user.type.internal_staff';
    protected string $userTypeInternalStaffDesc = 'Flag the user as Internal Staff';

    protected Connection $db;

    public function __construct(SchemaBuilder $schema)
    {
        parent::__construct($schema);
        $this->db = $this->schema->getConnection();
    }

    /**
     * Run the migration
     */
    public function up(): void
    {
        // I know... look shit but it is late, I am sleepy and want to finish this...

        // add the staff group
        $this->db->table('groups')
            ->insert([
                'id' => $this->staffInternalID,
                'name' => $this->staffInternalText,
            ]);

        // add the privileges
        $this->db->table('privileges')
            ->insert([
                'name' => $this->userTypeInternalStaffText,
                'description' => $this->userTypeInternalStaffDesc,
            ]);

        // we want the ID of the privilege we add
        $this->userTypeInternalStaffID = $this->getPrivilegeId($this->userTypeInternalStaffText);

        // Time to add shit to the groups
        $this->insertGroupPermission($this->userTypeInternalStaffID, $this->staffInternalID);
        $this->insertGroupPermission($this->userTypeInternalStaffID, $this->shiftCoordinatorID);
        $this->insertGroupPermission($this->userTypeInternalStaffID, $this->bureaucratID);
        $this->insertGroupPermission($this->userTypeInternalStaffID, $this->developerID);
    }

    /**
     * Reverse the migration
     */
    public function down(): void
    {
        // we want the ID of the privilege we add
        $this->userTypeInternalStaffID = $this->getPrivilegeId($this->userTypeInternalStaffText);

        // Time to delete the crap out of the system...
        $this->deleteGroupPermission($this->userTypeInternalStaffID, $this->staffInternalID);
        $this->deleteGroupPermission($this->userTypeInternalStaffID, $this->shiftCoordinatorID);
        $this->deleteGroupPermission($this->userTypeInternalStaffID, $this->bureaucratID);
        $this->deleteGroupPermission($this->userTypeInternalStaffID, $this->developerID);

        $this->deletePermission($this->userTypeInternalStaffText);

        $this->deleteGroup($this->staffInternalID);
    }


    /**
     * Retrieves the ID of a given privilege.
     *
     * @param string $privilege The name of the privilege whose ID is to be retrieved.
     * @return int The ID of the privilege.
     */
    protected function getPrivilegeId(string $privilege): int
    {
        return $this->db->table('privileges')
            ->where('name', $privilege)
            ->get(['id'])
            ->first()->id;
    }

    /**
     * Deletes a group identified by the provided ID.
     *
     * @param int $group The ID of the group to be deleted.
     * @return void
     */
    protected function deleteGroup(int $group): void
    {
        $this->db->table('groups')
            ->where(['id' => $group])
            ->delete();
    }

    /**
     * Inserts a group into the database and assigns the given privileges to it.
     *
     * @param int $id The ID of the group.
     * @param string $name The name of the group.
     * @param array $privileges A list of privileges to be assigned to the group.
     *
     * @return void
     */
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

    /**
     * Removes a specified privilege from a group.
     *
     * @param int $privilege The ID of the privilege to remove.
     * @param int $group The ID of the group from which the privilege should be removed.
     * @return void
     */
    protected function deleteGroupPermission(int $privilege, int $group): void
    {
        $this->db->table('group_privileges')
            ->where(['group_id' => $group, 'privilege_id' => $privilege])
            ->delete();
    }

    /**
     * Inserts a privilege into the group's privilege list.
     *
     * @param int $privilege The ID of the privilege to be added.
     * @param int $group The ID of the group to which the privilege is to be added.
     * @return void
     */
    protected function insertGroupPermission(int $privilege, int $group): void
    {
        $this->db->table('group_privileges')
            ->insertOrIgnore([
                ['group_id' => $group, 'privilege_id' => $privilege],
            ]);
    }

    /**
     * Moves a permission from one group to another.
     *
     * @param int $privilege The identifier of the privilege to be moved.
     * @param int $oldGroup The identifier of the group from which the privilege will be removed.
     * @param int $newGroup The identifier of the group to which the privilege will be added.
     * @return void
     */
    protected function movePermission(int $privilege, int $oldGroup, int $newGroup): void
    {
        $this->insertGroupPermission($privilege, $newGroup);
        $this->deleteGroupPermission($privilege, $oldGroup);
    }

    /**
     * Inserts a new permission into the privileges table and assigns it to a group.
     *
     * @param string $name The name of the permission.
     * @param string $description A description of the permission.
     * @param int $group The ID of the group to associate with the permission.
     * @return void
     */
    protected function insertPermission(string $name, string $description, int $group): void
    {
        $this->db->table('privileges')
            ->insertOrIgnore([
                'name' => $name, 'description' => $description,
            ]);
        $permission = $this->getPrivilegeId($name);
        $this->insertGroupPermission($permission, $group);
    }

    /**
     * Deletes a permission from the privileges table based on its name.
     *
     * @param string $privilege The name of the permission to delete.
     * @return void
     */
    protected function deletePermission(string $privilege): void
    {
        $this->db->table('privileges')
            ->where(['name' => $privilege])
            ->delete();
    }

    /**
     * Updates an existing permission in the privileges table.
     *
     * @param string $oldName The current name of the permission to be updated.
     * @param string $newName The new name of the permission.
     * @param string $description The new description of the permission.
     * @return void
     */
    protected function updatePermission(string $oldName, string $newName, string $description): void
    {
        $this->db->table('privileges')->where('name', $oldName)->update([
            'name' => $newName,
            'description' => $description,
        ]);
    }
}
