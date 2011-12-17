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