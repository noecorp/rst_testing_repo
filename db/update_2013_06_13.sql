SET @flag_id := (SELECT id FROM `t_flags` WHERE name='operation-reports');
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'userlogin', @flag_id, 'Login Report');
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'exportuserlogin', @flag_id, 'Export Login Report');
