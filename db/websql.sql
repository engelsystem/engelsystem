

-- DeleteLog

CREATE TABLE `DeleteLog` (
  `id` int(11) NOT NULL,
  `tablename` varchar(30) NOT NULL,
  `entry_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
ALTER TABLE `DeleteLog` ADD PRIMARY KEY (`id`);
ALTER TABLE `DeleteLog` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;


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

