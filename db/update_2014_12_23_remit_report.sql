SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-remit_reports'); 

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'searchremit', @flag_id, 'Remittance report');

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'exportsearchremit', @flag_id, 'Remittance report');
