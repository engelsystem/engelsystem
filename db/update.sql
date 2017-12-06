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

-- DeleteLog
CREATE TABLE `DeleteLog` (
  `id` int(11) NOT NULL,
  `tablename` varchar(30) NOT NULL,
  `entry_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
ALTER TABLE `DeleteLog` ADD PRIMARY KEY (`id`);
ALTER TABLE `DeleteLog` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
-- Insert dummy-entry to have at least one entry to pass to clients for the lastid
INSERT INTO `DeleteLog` (`tablename`, `entry_id`) VALUES ('no-op', '123456789');

-- Updated Microseconds
ALTER TABLE `Shifts` ADD `updated_microseconds` DOUBLE NOT NULL DEFAULT '10' AFTER `edited_at_timestamp`;
ALTER TABLE `Shifts` ADD INDEX( `updated_microseconds`);
UPDATE Shifts SET updated_microseconds = SID;

ALTER TABLE `User` ADD `updated_microseconds` DOUBLE NOT NULL DEFAULT '10' AFTER `email_by_human_allowed`;
ALTER TABLE `User` ADD INDEX( `updated_microseconds`);
UPDATE User SET updated_microseconds = UID;

ALTER TABLE `Room` ADD `updated_microseconds` DOUBLE NOT NULL DEFAULT '10' AFTER `Number`;
ALTER TABLE `Room` ADD INDEX( `updated_microseconds`);
UPDATE Room SET updated_microseconds = RID;

ALTER TABLE `ShiftEntry` ADD `updated_microseconds` DOUBLE NOT NULL DEFAULT '10' AFTER `freeloaded`;
ALTER TABLE `ShiftEntry` ADD INDEX( `updated_microseconds`);
UPDATE ShiftEntry SET updated_microseconds = id;

ALTER TABLE `ShiftTypes` ADD `updated_microseconds` DOUBLE NOT NULL DEFAULT '10' AFTER `description`;
ALTER TABLE `ShiftTypes` ADD INDEX( `updated_microseconds`);
UPDATE ShiftTypes SET updated_microseconds = id;

ALTER TABLE `AngelTypes` ADD `updated_microseconds` DOUBLE NOT NULL DEFAULT '10' AFTER `contact_email`;
ALTER TABLE `AngelTypes` ADD INDEX( `updated_microseconds`);
UPDATE AngelTypes SET updated_microseconds = id;

ALTER TABLE `NeededAngelTypes` ADD `updated_microseconds` DOUBLE NOT NULL DEFAULT '10' AFTER `count`;
ALTER TABLE `NeededAngelTypes` ADD INDEX( `updated_microseconds`);
UPDATE NeededAngelTypes SET updated_microseconds = id;

