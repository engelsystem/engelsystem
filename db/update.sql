/* angeltype view */
INSERT INTO `engelsystem`.`Privileges` (`id`, `name`, `desc`) VALUES (NULL , 'angeltypes', 'View angeltypes');

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
