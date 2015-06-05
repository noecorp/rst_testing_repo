SET @flag_id := (SELECT id FROM `t_flags` WHERE name='operation-settings ');

INSERT INTO `t_privileges` (`id` ,`name` ,`flag_id` ,`description`) VALUES (NULL , 'manageifsc', @flag_id, 'Manage IFSC');
SET @privilege_id = last_insert_id();

INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, 3, @flag_id, @privilege_id, 1);
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, 4, @flag_id, @privilege_id, 1);
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, 8, @flag_id, @privilege_id, 1);
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, 9, @flag_id, @privilege_id, 1);

INSERT INTO `t_privileges` (`id` ,`name` ,`flag_id` ,`description`) VALUES (NULL , 'addifsc', @flag_id, 'Add IFSC Details');
SET @privilege_id = last_insert_id();

INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, 3, @flag_id, @privilege_id, 1);
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, 4, @flag_id, @privilege_id, 1);
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, 8, @flag_id, @privilege_id, 1);
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, 9, @flag_id, @privilege_id, 1);

INSERT INTO `t_privileges` (`id` ,`name` ,`flag_id` ,`description`) VALUES (NULL , 'updateifsc', @flag_id, 'Update IFSC Details');
SET @privilege_id = last_insert_id();

INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, 3, @flag_id, @privilege_id, 1);
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, 4, @flag_id, @privilege_id, 1);
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, 8, @flag_id, @privilege_id, 1);
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, 9, @flag_id, @privilege_id, 1);

