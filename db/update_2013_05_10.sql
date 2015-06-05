
INSERT INTO `t_flags` (`name`, `description`, `active_on_dev`, `active_on_prod`) VALUES ('agent-remit_boi_reports', 'Remittance Reports', '1', '0');
SET @flag_id_val = last_insert_id();
Insert into `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'remittancereport', @flag_id_val, 'Remittance Report');
SET @privilege_id = last_insert_id();
Insert into `t_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, 38, @flag_id_val, @privilege_id, '1');
Insert into `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'exportremittancereport', @flag_id_val, 'Export Remittance Report');
SET @privilege_id = last_insert_id();
Insert into `t_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, 38, @flag_id_val, @privilege_id, '1');
