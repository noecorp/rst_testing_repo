SET @flag_id := (SELECT id FROM `t_flags` WHERE name='agent-remit_boi_reports');
Insert into `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'remittancecommission', @flag_id, 'Agent Remittance Commission Report');
SET @privilege_id = last_insert_id();
Insert into `t_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, 38, @flag_id, @privilege_id, '1');
Insert into `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'exportremittancecommission', @flag_id, 'Export Remittance Commission Report');
SET @privilege_id = last_insert_id();
Insert into `t_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, 38, @flag_id, @privilege_id, '1');


