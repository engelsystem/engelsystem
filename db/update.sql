
/* introduce user arrival date */
ALTER TABLE `User` ADD `arrival_date` INT NULL ,
ADD `planned_arrival_date` INT NOT NULL ,
ADD INDEX ( `arrival_date` , `planned_arrival_date` ) ;

/* fix log */
ALTER TABLE `LogEntries` CHANGE `nick` `nick` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ;

/* introduce got-voucher flag */
ALTER TABLE `User` ADD `got_voucher` BOOLEAN NOT NULL;

/* introduce shift types */
CREATE TABLE IF NOT EXISTS `ShiftTypes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `angeltype_id` int(11) DEFAULT NULL,
  `description` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;
ALTER TABLE `ShiftTypes` ADD INDEX ( `angeltype_id` );
ALTER TABLE `ShiftTypes` ADD FOREIGN KEY ( `angeltype_id` ) REFERENCES `AngelTypes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
INSERT INTO `Privileges` (`id`, `name`, `desc`) VALUES (NULL , 'shifttypes', 'Administrate shift types');
INSERT INTO `GroupPrivileges` SET `group_id`=-5, `privilege_id`=(SELECT `id` FROM `Privileges` WHERE `name`='shifttypes');

ALTER TABLE `Shifts` ADD `shifttype_id` INT NOT NULL AFTER `SID`, ADD INDEX ( `shifttype_id` );
UPDATE `Shifts` SET `name`='' WHERE `name` IS NULL;
INSERT INTO `ShiftTypes` SELECT DISTINCT NULL , `name` , NULL , '' FROM `Shifts`;
UPDATE `Shifts` SET `shifttype_id`=(SELECT `id` FROM `ShiftTypes` WHERE `ShiftTypes`.`name`=`Shifts`.`name`);
ALTER TABLE `Shifts` ADD `title` TEXT NULL AFTER `SID`;
ALTER TABLE `Shifts` ADD FOREIGN KEY ( `shifttype_id` ) REFERENCES `ShiftTypes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE `Shifts` DROP `name`;

/* cleanup */
ALTER TABLE `User` DROP `ICQ` ;

/* opt-in field for user shiftinfo mails */
ALTER TABLE `User` ADD `email_shiftinfo` BOOLEAN NOT NULL DEFAULT FALSE COMMENT 'User wants to be informed by mail about changes in his shifts' AFTER `email` ;

/* fix questions */
ALTER TABLE `Questions` CHANGE `AID` `AID` INT( 11 ) NULL DEFAULT NULL ;

/* teamcoordinators */
ALTER TABLE `UserAngelTypes` ADD `coordinator` BOOLEAN NOT NULL;
ALTER TABLE `UserAngelTypes` ADD INDEX ( `coordinator` );

/* angeltype view */
INSERT INTO `Privileges` (`id`, `name`, `desc`) VALUES (NULL , 'angeltypes', 'View angeltypes');

/* force active */
ALTER TABLE `User` ADD `force_active` BOOLEAN NOT NULL AFTER `Aktiv`, ADD INDEX ( `force_active` );

/* freeloader */
ALTER TABLE `ShiftEntry` ADD `freeloaded` BOOLEAN NOT NULL, ADD INDEX ( `freeloaded` );
ALTER TABLE `ShiftEntry` ADD `freeload_comment` TEXT NULL DEFAULT NULL;

/* password recovery */
ALTER TABLE `User` ADD `password_recovery_token` VARCHAR( 32 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL AFTER `Passwort` ,
ADD INDEX ( `password_recovery_token` );

/* Update für #27, iCal-Export */
ALTER TABLE `User` ADD `ical_key` VARCHAR( 32 ) NOT NULL;
ALTER TABLE `User` ADD INDEX ( `ical_key` );

INSERT INTO `Privileges` (
`id` ,
`name` ,
`desc`
)
VALUES (
NULL , 'ical', 'iCal shift export'
);

/* DECT Nummern können für GSM auch 5-stellig sein. */
ALTER TABLE `User` CHANGE `DECT` `DECT` VARCHAR( 5 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;

/* Neues Engeltypen-System */
ALTER TABLE `AngelTypes` DROP `Man`;
ALTER TABLE `AngelTypes` CHANGE `TID` `id` INT( 11 ) NOT NULL AUTO_INCREMENT;
ALTER TABLE `AngelTypes` CHANGE `Name` `name` VARCHAR( 25 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '';
ALTER TABLE `AngelTypes` ADD `restricted` INT( 1 ) NOT NULL;
ALTER TABLE `AngelTypes` ADD `description` TEXT NOT NULL;