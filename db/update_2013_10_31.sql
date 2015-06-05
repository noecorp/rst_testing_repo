SET @flag_id := (SELECT id FROM `t_flags` WHERE name = 'operation-reports');

INSERT INTO `t_privileges`(`name`,`flag_id`,`description`) values('customerregistration',@flag_id,'Customer Registration Report');
SET @priv_id = last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, '3', @flag_id, @priv_id, '1');


INSERT INTO `t_privileges`(`name`,`flag_id`,`description`) values('exportcustomerregistration',@flag_id,'Export Customer Registration Report');
SET @priv_id = last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, '3', @flag_id, @priv_id, '1');


