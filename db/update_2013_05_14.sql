SET @flag_id := (SELECT id FROM `t_flags` WHERE name='agent-reports');
Insert into `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'agentcommissionsummary', @flag_id, 'Agent Commission Summary Report');
SET @privilege_id = last_insert_id();
SET @product_id := (select id from t_products where name='AXIS BANK SHMARTPAY PREPAID');
Insert into `t_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @product_id, @flag_id, @privilege_id, '1');
Insert into `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'exportagentcommissionsummary', @flag_id, 'Export Agent Commission Summary Report');
SET @privilege_id = last_insert_id();
Insert into `t_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @product_id, @flag_id, @privilege_id, '1');