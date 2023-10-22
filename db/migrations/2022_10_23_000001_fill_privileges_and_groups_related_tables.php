<?php

declare(strict_types=1);

namespace Engelsystem\Migrations;

use Engelsystem\Database\Migration\Migration;
use stdClass;

class FillPrivilegesAndGroupsRelatedTables extends Migration
{
    use ChangesReferences;
    use Reference;

    /**
     * Inserts missing data into permissions & groups related tables
     */
    public function up(): void
    {
        $db = $this->schema->getConnection();
        if ($db->table('privileges')->count() > 0) {
            return;
        }

        $db->table('groups')
            ->insert([
                ['id' => 10, 'name' => 'Guest'],
                ['id' => 20, 'name' => 'Angel'],
                ['id' => 30, 'name' => 'Welcome Angel'],
                ['id' => 35, 'name' => 'Voucher Angel'],
                ['id' => 50, 'name' => 'Shirt Manager'],
                ['id' => 60, 'name' => 'Shift Coordinator'],
                ['id' => 65, 'name' => 'Team Coordinator'],
                ['id' => 80, 'name' => 'Bureaucrat'],
                ['id' => 85, 'name' => 'News Admin'],
                ['id' => 90, 'name' => 'Developer'],
            ]);

        $db->table('privileges')
            ->insert([
                ['id' => 1, 'name' => 'start', 'description' => 'Startseite für Gäste/Nicht eingeloggte User'],
                ['id' => 2, 'name' => 'login', 'description' => 'Logindialog'],
                ['id' => 3, 'name' => 'news', 'description' => 'Anzeigen der News-Seite'],
                ['id' => 4, 'name' => 'logout', 'description' => 'User darf sich ausloggen'],
                ['id' => 5, 'name' => 'register', 'description' => 'Einen neuen Engel registerieren'],
                ['id' => 6, 'name' => 'admin_rooms', 'description' => 'Räume administrieren'],
                ['id' => 7, 'name' => 'admin_angel_types', 'description' => 'Engel Typen administrieren'],
                ['id' => 8, 'name' => 'user_settings', 'description' => 'User profile settings'],
                ['id' => 9, 'name' => 'user_messages',
                    'description' => 'Writing and reading messages from user to user'],
                ['id' => 10, 'name' => 'admin_groups', 'description' => 'Manage usergroups and their rights'],
                ['id' => 14, 'name' => 'admin_news', 'description' => 'Administrate the news section'],
                ['id' => 15, 'name' => 'news_comments', 'description' => 'User can comment news'],
                ['id' => 16, 'name' => 'admin_user', 'description' => 'Administrate the angels'],
                ['id' => 17, 'name' => 'user_meetings', 'description' => 'Lists meetings (news)'],
                ['id' => 18, 'name' => 'admin_language', 'description' => 'Translate the system'],
                ['id' => 19, 'name' => 'admin_log', 'description' => 'Display recent changes'],
                ['id' => 21, 'name' => 'schedule.import', 'description' => 'Import rooms and shifts from schedule.xml'],
                ['id' => 24, 'name' => 'user_shifts', 'description' => 'Signup for shifts'],
                ['id' => 25, 'name' => 'user_shifts_admin', 'description' => 'Signup other angels for shifts.'],
                ['id' => 26, 'name' => 'user_myshifts',
                    'description' => 'Allow angels to view their own shifts and cancel them.'],
                ['id' => 27, 'name' => 'admin_arrive', 'description' => 'Mark angels when they arrive.'],
                ['id' => 28, 'name' => 'admin_shifts', 'description' => 'Create shifts'],
                ['id' => 30, 'name' => 'ical', 'description' => 'iCal shift export'],
                ['id' => 31, 'name' => 'admin_active',
                    'description' => 'Mark angels as active and if they got a t-shirt.'],
                ['id' => 32, 'name' => 'admin_free', 'description' => 'Show a list of free/unemployed angels.'],
                ['id' => 33, 'name' => 'admin_user_angeltypes', 'description' => 'Confirm restricted angel types'],
                ['id' => 34, 'name' => 'atom', 'description' => ' Atom news export'],
                ['id' => 35, 'name' => 'shifts_json_export', 'description' => 'Export shifts in JSON format'],
                ['id' => 36, 'name' => 'angeltypes', 'description' => 'View angeltypes'],
                ['id' => 37, 'name' => 'user_angeltypes', 'description' => 'Join angeltypes.'],
                ['id' => 38, 'name' => 'shifttypes', 'description' => 'Administrate shift types'],
                ['id' => 39, 'name' => 'admin_event_config', 'description' => 'Allow editing event config'],
                ['id' => 40, 'name' => 'view_rooms', 'description' => 'User can view rooms'],
                ['id' => 41, 'name' => 'shiftentry_edit_angeltype_supporter',
                    'description' => 'If user with this privilege is angeltype supporter, '
                        . 'he can put users in shifts for their angeltype'],
                ['id' => 43, 'name' => 'admin_user_worklog', 'description' => 'Manage user work log entries.'],
                ['id' => 44, 'name' => 'faq.view', 'description' => 'View FAQ entries'],
                ['id' => 45, 'name' => 'faq.edit', 'description' => 'Edit FAQ entries'],
                ['id' => 46, 'name' => 'question.add', 'description' => 'Ask questions'],
                ['id' => 47, 'name' => 'question.edit', 'description' => 'Answer questions'],
                ['id' => 48, 'name' => 'user.edit.shirt', 'description' => 'Edit user shirts'],
                ['id' => 49, 'name' => 'voucher.edit', 'description' => 'Edit vouchers'],
            ]);

        $db->table('group_privileges')->insert([
            ['id' => 23, 'group_id' => 10, 'privilege_id' => 2],
            ['id' => 24, 'group_id' => 10, 'privilege_id' => 5],
            ['id' => 85, 'group_id' => 90, 'privilege_id' => 10],
            ['id' => 86, 'group_id' => 90, 'privilege_id' => 21],
            ['id' => 87, 'group_id' => 90, 'privilege_id' => 18],
            ['id' => 88, 'group_id' => 10, 'privilege_id' => 1],
            ['id' => 206, 'group_id' => 80, 'privilege_id' => 31],
            ['id' => 207, 'group_id' => 80, 'privilege_id' => 7],
            ['id' => 209, 'group_id' => 80, 'privilege_id' => 21],
            ['id' => 210, 'group_id' => 80, 'privilege_id' => 14],
            ['id' => 212, 'group_id' => 80, 'privilege_id' => 6],
            ['id' => 213, 'group_id' => 80, 'privilege_id' => 28],
            ['id' => 214, 'group_id' => 80, 'privilege_id' => 16],
            ['id' => 215, 'group_id' => 80, 'privilege_id' => 33],
            ['id' => 216, 'group_id' => 80, 'privilege_id' => 5],
            ['id' => 218, 'group_id' => 60, 'privilege_id' => 39],
            ['id' => 219, 'group_id' => 65, 'privilege_id' => 14],
            ['id' => 220, 'group_id' => 65, 'privilege_id' => 33],
            ['id' => 221, 'group_id' => 65, 'privilege_id' => 25],
            ['id' => 235, 'group_id' => 60, 'privilege_id' => 27],
            ['id' => 236, 'group_id' => 60, 'privilege_id' => 32],
            ['id' => 237, 'group_id' => 60, 'privilege_id' => 19],
            ['id' => 238, 'group_id' => 60, 'privilege_id' => 14],
            ['id' => 239, 'group_id' => 60, 'privilege_id' => 28],
            ['id' => 240, 'group_id' => 60, 'privilege_id' => 16],
            ['id' => 241, 'group_id' => 60, 'privilege_id' => 5],
            ['id' => 242, 'group_id' => 60, 'privilege_id' => 25],
            ['id' => 243, 'group_id' => 20, 'privilege_id' => 36],
            ['id' => 244, 'group_id' => 20, 'privilege_id' => 34],
            ['id' => 245, 'group_id' => 20, 'privilege_id' => 30],
            ['id' => 246, 'group_id' => 20, 'privilege_id' => 4],
            ['id' => 247, 'group_id' => 20, 'privilege_id' => 3],
            ['id' => 248, 'group_id' => 20, 'privilege_id' => 15],
            ['id' => 249, 'group_id' => 20, 'privilege_id' => 35],
            ['id' => 250, 'group_id' => 20, 'privilege_id' => 37],
            ['id' => 251, 'group_id' => 20, 'privilege_id' => 17],
            ['id' => 252, 'group_id' => 20, 'privilege_id' => 9],
            ['id' => 253, 'group_id' => 20, 'privilege_id' => 26],
            ['id' => 255, 'group_id' => 20, 'privilege_id' => 8],
            ['id' => 256, 'group_id' => 20, 'privilege_id' => 24],
            ['id' => 257, 'group_id' => 80, 'privilege_id' => 38],
            ['id' => 258, 'group_id' => 50, 'privilege_id' => 31],
            ['id' => 259, 'group_id' => 20, 'privilege_id' => 40],
            ['id' => 260, 'group_id' => 85, 'privilege_id' => 14],
            ['id' => 262, 'group_id' => 60, 'privilege_id' => 43],
            ['id' => 263, 'group_id' => 20, 'privilege_id' => 41],
            ['id' => 264, 'group_id' => 30, 'privilege_id' => 27],
            ['id' => 265, 'group_id' => 10, 'privilege_id' => 44],
            ['id' => 266, 'group_id' => 20, 'privilege_id' => 44],
            ['id' => 267, 'group_id' => 60, 'privilege_id' => 45],
            ['id' => 268, 'group_id' => 20, 'privilege_id' => 46],
            ['id' => 269, 'group_id' => 60, 'privilege_id' => 47],
            ['id' => 270, 'group_id' => 60, 'privilege_id' => 48],
            ['id' => 271, 'group_id' => 50, 'privilege_id' => 48],
            ['id' => 272, 'group_id' => 50, 'privilege_id' => 27],
            ['id' => 273, 'group_id' => 60, 'privilege_id' => 49],
            ['id' => 274, 'group_id' => 35, 'privilege_id' => 49],
            ['id' => 275, 'group_id' => 35, 'privilege_id' => 27],
        ]);

        /** @var stdClass $admin */
        $admin = $db->table('users')->where('name', 'admin')->first();
        if (!$admin) {
            return;
        }

        // Angel, ShiCo, Team coordinator, Bureaucrat, Dev
        foreach ([20, 60, 65, 80, 90] as $group) {
            $db->table('users_groups')->insert(['user_id' => $admin->id, 'group_id' => $group]);
        }
    }
}
