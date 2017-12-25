INSERT INTO `Privileges` (`id`, `name`, `desc`) VALUES (40, 'view_rooms', 'User can view rooms');
INSERT INTO `GroupPrivileges` (`id`, `group_id`, `privilege_id`) VALUES (NULL, '-2', '40');

ALTER TABLE `UserAngelTypes` CHANGE `coordinator` `supporter` BOOLEAN;

ALTER TABLE `User` ADD COLUMN `email_by_human_allowed` BOOLEAN NOT NULL;

-- No Self Sign Up for some Angel Types
ALTER TABLE AngelTypes ADD no_self_signup TINYINT(1) NOT NULL;

ALTER TABLE `AngelTypes`
  ADD `contact_user_id` INT NULL,
  ADD `contact_name` VARCHAR(250) NULL,
  ADD `contact_dect` VARCHAR(5) NULL,
  ADD `contact_email` VARCHAR(250) NULL,
  ADD INDEX  (`contact_user_id`);
ALTER TABLE `AngelTypes`
  ADD  FOREIGN KEY (`contact_user_id`) REFERENCES `User`(`UID`) ON DELETE SET NULL ON UPDATE CASCADE;

INSERT INTO `Privileges` (`id`, `name`, `desc`) VALUES (NULL, 'shiftentry_edit_angeltype_supporter', 'If user with this privilege is angeltype supporter, he can put users in shifts for their angeltype');

-- DB Performance
ALTER TABLE `Shifts` ADD INDEX(`start`);
ALTER TABLE `NeededAngelTypes` ADD INDEX(`count`);

-- Security
UPDATE `Groups` SET UID = UID * 10;
INSERT INTO `Groups` (Name, UID) VALUES ('News Admin', -65);
INSERT INTO `Privileges` (id, name, `desc`) VALUES (42, 'admin_news_html', 'Use HTML in news');
INSERT INTO `GroupPrivileges` (group_id, privilege_id) VALUES (-65, 14), (-65, 42);

-- Add log level to LogEntries
ALTER TABLE `LogEntries` CHANGE COLUMN `nick` `level` VARCHAR(20) NOT NULL;

-- Angeltype contact update
ALTER TABLE `AngelTypes` DROP FOREIGN KEY angeltypes_ibfk_1;
ALTER TABLE `AngelTypes` DROP `contact_user_id`;

-- Room update
ALTER TABLE `Room` DROP `Number`;
ALTER TABLE `Room` DROP `show`;
ALTER TABLE `Room` DROP `Man`;
ALTER TABLE `Room` ADD `from_frab` BOOLEAN NOT NULL AFTER `FromPentabarf`;
UPDATE Room SET `from_frab` = (`FromPentabarf` = 'Y');
ALTER TABLE `Room` DROP `FromPentabarf`;
ALTER TABLE `Room` ADD `map_url` VARCHAR(300) NULL AFTER `from_frab`;
ALTER TABLE `Room` ADD `description` TEXT NULL AFTER `map_url`;

-- Dashboard
ALTER TABLE `AngelTypes` ADD `show_on_dashboard` BOOLEAN NOT NULL AFTER `contact_email`;
UPDATE `AngelTypes` SET `show_on_dashboard` = TRUE;
