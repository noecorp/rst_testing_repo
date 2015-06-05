SET @flag_id := (SELECT id FROM `t_flags` WHERE name='operation-history');
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'commission', @flag_id, 'Commission Logs');
