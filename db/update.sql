INSERT INTO `Privileges` (`id`, `name`, `desc`) VALUES (40, 'view_rooms', 'User can view rooms');
INSERT INTO `GroupPrivileges` (`id`, `group_id`, `privilege_id`) VALUES (NULL, '-2', '40');

ALTER TABLE `UserAngelTypes` CHANGE `coordinator` `supporter` BOOLEAN;

ALTER TABLE `User` ADD COLUMN `email_by_human_allowed` BOOLEAN NOT NULL;

-- No Self Sign Up for some Angel Types
ALTER TABLE engelsystem.AngelTypes ADD no_self_signup TINYINT(1) NOT NULL;