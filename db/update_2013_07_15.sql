SET @flag_id := (SELECT id FROM `t_flags` WHERE name='operation-reports');
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'loginsummary', @flag_id, 'Login summary report for Agent and Operation');
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'exportloginsummary', @flag_id, 'Export Login summary report for Agent and Operation');
