SET @ops_id := 3;
SET @flag_id := (SELECT id FROM `t_flags` WHERE name='operation-agentsummary' LIMIT 1);
Insert into `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'closeaccount', @flag_id, 'Close Agent Account');
SET @priv_id = last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

