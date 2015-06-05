SET @flag_id := (SELECT id FROM `t_flags` WHERE name='operation-agents');
INSERT INTO `t_privileges` (`name`, `flag_id`, `description`) VALUES ('addauthemail', @flag_id, 'Add Auth email');
