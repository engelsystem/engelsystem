

-- DeleteLog

CREATE TABLE `DeleteLog` (
  `id` int(11) NOT NULL,
  `tablename` varchar(30) NOT NULL,
  `entry_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


-- Updated Microseconds

ALTER TABLE `Shifts` ADD `updated_microseconds` DOUBLE NOT NULL DEFAULT '10' AFTER `edited_at_timestamp`;
ALTER TABLE `Shifts` ADD INDEX( `updated_microseconds`);

ALTER TABLE `User` ADD `updated_microseconds` DOUBLE NOT NULL DEFAULT '10' AFTER `email_by_human_allowed`;
ALTER TABLE `User` ADD INDEX( `updated_microseconds`);

ALTER TABLE `Room` ADD `updated_microseconds` DOUBLE NOT NULL DEFAULT '10' AFTER `Number`;
ALTER TABLE `Room` ADD INDEX( `updated_microseconds`);

ALTER TABLE `ShiftEntry` ADD `updated_microseconds` DOUBLE NOT NULL DEFAULT '10' AFTER `freeloaded`;
ALTER TABLE `ShiftEntry` ADD INDEX( `updated_microseconds`);

ALTER TABLE `ShiftTypes` ADD `updated_microseconds` DOUBLE NOT NULL DEFAULT '10' AFTER `description`;
ALTER TABLE `ShiftTypes` ADD INDEX( `updated_microseconds`);

ALTER TABLE `AngelTypes` ADD `updated_microseconds` DOUBLE NOT NULL DEFAULT '10' AFTER `contact_email`;
ALTER TABLE `AngelTypes` ADD INDEX( `updated_microseconds`);

ALTER TABLE `NeededAngelTypes` ADD `updated_microseconds` DOUBLE NOT NULL DEFAULT '10' AFTER `count`;
ALTER TABLE `NeededAngelTypes` ADD INDEX( `updated_microseconds`);

