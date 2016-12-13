INSERT INTO `Privileges` (`id`, `name`, `desc`) VALUES (40, 'view_rooms', 'User can view rooms');
INSERT INTO `GroupPrivileges` (`id`, `group_id`, `privilege_id`) VALUES (NULL, '-2', '40');

ALTER TABLE `UserAngelTypes` CHANGE `coordinator` `supporter` BOOLEAN;

ALTER TABLE `User` ADD COLUMN `email_by_human_allowed` BOOLEAN NOT NULL;

-- No Self Sign Up for some Angel Types
ALTER TABLE engelsystem.AngelTypes ADD no_self_signup TINYINT(1) NOT NULL;

ALTER TABLE `AngelTypes`  
  ADD `contact_user_id` INT NULL,  
  ADD `contact_name` VARCHAR(250) NULL,  
  ADD `contact_dect` VARCHAR(5) NULL,  
  ADD `contact_email` VARCHAR(250) NULL,  
  ADD INDEX  (`contact_user_id`);
ALTER TABLE `AngelTypes` 
  ADD  FOREIGN KEY (`contact_user_id`) REFERENCES `User`(`UID`) ON DELETE SET NULL ON UPDATE CASCADE;
