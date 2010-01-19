ALTER TABLE `UserCVS` ADD `GroupID` INT NULL AFTER `UID` ;
ALTER TABLE `UserCVS` ADD `admin\group.php` CHAR( 1 ) NOT NULL DEFAULT 'N' AFTER `admin/userDefaultSetting.php` ;

INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('admin/group.php', 'DE', 'Benutzer Gruppen');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('admin/group.php', 'EN', 'User Group');
