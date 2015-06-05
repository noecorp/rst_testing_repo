SET @group_id := 3;
SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-agentsummary'); 
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'remapping', @flag_id, 'Agent Remapping');
SET @priv_id = last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @group_id, @flag_id, @priv_id, '1');

