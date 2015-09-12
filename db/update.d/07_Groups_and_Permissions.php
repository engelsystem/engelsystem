<?php
// Most complex update yet. Let's go...

_rename_table("UserGroups", "Groups");

if(sql_num_query("SHOW TABLES LIKE 'UserCVS'") === 1 && sql_num_query("SHOW TABLES LIKE 'UserGroups'") === 0) {
    // First of all, create a separate table for group assignments of users
    sql_query("CREATE TABLE `UserGroups` (
                  `id` int(11) NOT NULL AUTO_INCREMENT,
                  `uid` int(11) NOT NULL,
                  `group_id` int(11) NOT NULL,
                  PRIMARY KEY (`id`),
                  KEY `uid` (`uid`,`group_id`),
                  KEY `group_id` (`group_id`)
                )");
    // ...and fill it with the old data
    sql_query("INSERT INTO UserGroups (`uid`, `group_id`) SELECT `UID`, `GroupID` FROM `UserCVS` WHERE `UID` > 0");

    if(sql_num_query("SHOW TABLES LIKE 'Privileges'") == 0) {
        // Then create a separate table that stores the available privileges...
        sql_query("CREATE TABLE IF NOT EXISTS `Privileges` (
                      `id` int(11) NOT NULL AUTO_INCREMENT,
                      `name` varchar(128) NOT NULL,
                      `desc` varchar(1024) NOT NULL,
                      PRIMARY KEY (`id`),
                      UNIQUE KEY `name` (`name`)
                    )");
        // ...and fill it with genuine data. We cannot determine these from the old data!
        sql_query("INSERT INTO `Privileges` (`id`, `name`, `desc`) VALUES
                    (1, 'start', 'Startseite für Gäste/Nicht eingeloggte User'),
                    (2, 'login', 'Logindialog'),
                    (3, 'news', 'Anzeigen der News-Seite'),
                    (4, 'logout', 'User darf sich ausloggen'),
                    (5, 'register', 'Einen neuen Engel registerieren'),
                    (6, 'admin_rooms', 'Orte administrieren'),
                    (7, 'admin_angel_types', 'Engel Typen administrieren'),
                    (8, 'user_settings', 'User profile settings'),
                    (9, 'user_messages', 'Writing and reading messages from user to user'),
                    (10, 'admin_groups', 'Manage usergroups and their rights'),
                    (11, 'user_questions', 'Let users ask questions'),
                    (12, 'admin_questions', 'Answer user''s questions'),
                    (13, 'admin_faq', 'Edit FAQs'),
                    (14, 'admin_news', 'Administrate the news section'),
                    (15, 'news_comments', 'User can comment news'),
                    (16, 'admin_user', 'Administrate the angels'),
                    (17, 'user_meetings', 'Lists meetings (news)'),
                    (18, 'admin_language', 'Translate the system'),
                    (19, 'admin_log', 'Display recent changes'),
                    (20, 'user_wakeup', 'User wakeup-service organization'),
                    (21, 'admin_import', 'Import locations and shifts from pentabarf'),
                    (22, 'credits', 'View credits'),
                    (23, 'faq', 'View FAQ'),
                    (24, 'user_shifts', 'Signup for shifts'),
                    (25, 'user_shifts_admin', 'Signup other angels for shifts.'),
                    (26, 'user_myshifts', 'Allow angels to view their own shifts and cancel them.'),
                    (27, 'admin_arrive', 'Mark angels when they are available.'),
                    (28, 'admin_shifts', 'Create shifts'),
                    (30, 'ical', 'iCal shift export'),
                    (31, 'admin_active', 'Mark angels as active and if they got a t-shirt.'),
                    (32, 'admin_free', 'Show a list of free/unemployed angels.')
        ");
    }

    if(sql_num_query("SHOW TABLES LIKE 'GroupPrivileges'") == 0) {
        // Last, we create the table for the privileges a group can have
        sql_query("CREATE TABLE `GroupPrivileges` (
                      `id` int(11) NOT NULL AUTO_INCREMENT,
                      `group_id` int(11) NOT NULL,
                      `privilege_id` int(11) NOT NULL,
                      PRIMARY KEY (`id`),
                      KEY `group_id` (`group_id`,`privilege_id`)
                    )");

        // ...and fill it with data.
        /// XXX: We could determine this from the old UserCVS table, at lease partially!
        sqL_query("INSERT INTO `GroupPrivileges` (`id`, `group_id`, `privilege_id`) VALUES
                    (107, -2, 24),
                    (24, -1, 5),
                    (106, -2, 8),
                    (105, -2, 11),
                    (23, -1, 2),
                    (142, -5, 16),
                    (141, -5, 28),
                    (104, -2, 26),
                    (103, -2, 9),
                    (86, -6, 21),
                    (140, -5, 6),
                    (139, -5, 12),
                    (102, -2, 17),
                    (138, -5, 14),
                    (137, -5, 13),
                    (136, -5, 7),
                    (101, -2, 15),
                    (87, -6, 18),
                    (100, -2, 3),
                    (85, -6, 10),
                    (99, -2, 4),
                    (88, -1, 1),
                    (133, -3, 32),
                    (108, -2, 20),
                    (109, -4, 27),
                    (135, -5, 31),
                    (134, -3, 25),
                    (143, -5, 5);");
    }


    /* Hardest things last: We need to transform the old column-based system
     * with filename-based permissions to the new privileges system.
     *
     * For that to work, we need a manual mapping filename -> privilege, so we
     * can use the old data. So here we go:
     */

    #$files_to_privileges = array(
    #    "index.php" => "start",
    #    "logout.php" => "logout",
    #    "faq.php" => "faq",
    #    "makeuser.php" => "register",
    #    "nonpublic/index.php" => "login",
    #    "nonpublic/news.php" => "news",
    #    "nonpublic/news_comments.php" => "news_comments",
    #    "nonpublic/myschichtplan.php" => "",
    #    "nonpublic/myschichtplan_ical.php" => "",
    #    "nonpublic/schichtplan_beamer.php" => "",
    #    "nonpublic/engelbesprechung.php" => "",
    #    "nonpublic/schichtplan.php" => "",
    #    "nonpublic/schichtplan_add.php" => "",
    #    "nonpublic/wecken.php" => "",
    #    "nonpublic/waeckliste.php" => "",
    #    "nonpublic/messages.php" => "",
    #    "nonpublic/faq.php" => "",
    #    "nonpublic/einstellungen.php" => "",
    #    "Change T_Shirt Size" => "",
    #    "admin/index.php" => "",
    #    "admin/room.php" => "",
    #    "admin/EngelType.php" => "",
    #    "admin/schichtplan.php" => "",
    #    "admin/shiftadd.php" => "",
    #    "admin/schichtplan_druck.php" => "",
    #    "admin/user.php" => "",
    #    "admin/userChangeNormal.php" => "",
    #    "admin/userSaveNormal.php" => "",
    #    "admin/userChangeSecure.php" => "",
    #    "admin/userSaveSecure.php" => "",
    #    "admin/group.php" => "",
    #    "admin/userDefaultSetting.php" => "",
    #    "admin/UserPicture.php" => "",
    #    "admin/userArrived.php" => "",
    #    "admin/aktiv.php" => "",
    #    "admin/tshirt.php" => "",
    #    "admin/news.php" => "",
    #    "admin/faq.php" => "",
    #    "admin/free.php" => "",
    #    "admin/sprache.php" => "",
    #    "admin/dect.php" => "",
    #    "admin/dect_call.php" => "",
    #    "admin/dbUpdateFromXLS.php" => "",
    #    "admin/Recentchanges.php" => "",
    #    "admin/debug.php" => "",
    #    "Herald" => "",
    #    "Info" => "",
    #    "Conference" => "",
    #    "Kasse" => "",
    #    "Audio-Video" => "",
    #);

    $applied = true;
}
?>
