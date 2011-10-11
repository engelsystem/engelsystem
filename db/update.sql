/* Update für #27, iCal-Export */
ALTER TABLE `User` ADD `ical_key` VARCHAR( 32 ) NOT NULL ,
ADD UNIQUE (
`ical_key`
);

INSERT INTO `engelsystem`.`Privileges` (
`id` ,
`name` ,
`desc`
)
VALUES (
NULL , 'ical', 'iCal Schicht Export'
);