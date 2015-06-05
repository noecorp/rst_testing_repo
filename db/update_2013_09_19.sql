SET @product_id := (select id from t_products where name='Kotak Bank Shmart Transfer' LIMIT 1);

INSERT INTO `t_flags` (`id`, `name`, `description`, `active_on_dev`, `active_on_prod`) VALUES (NULL, 'agent-remit_kotak_reports', 'Reports for Kotak Remittance', '1', '0');
SET @flag_id = last_insert_id();

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'remittancereport', @flag_id, 'Remittance Report');
SET @privilege_id = last_insert_id();
Insert into `t_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @product_id, @flag_id, @privilege_id, '1');

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'exportremittancereport', @flag_id, 'Export Remittance Report');
SET @privilege_id = last_insert_id();
Insert into `t_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @product_id, @flag_id, @privilege_id, '1');

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'remittancecommission', @flag_id, 'Agent Remittance Commission Report');
SET @privilege_id = last_insert_id();
Insert into `t_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @product_id, @flag_id, @privilege_id, '1');

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'exportremittancecommission', @flag_id, 'Export Agent Remittance Commission Report');
SET @privilege_id = last_insert_id();
Insert into `t_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @product_id, @flag_id, @privilege_id, '1');

SET @priv_id := (select id from `t_privileges` where name='feereport' LIMIT 1);
SET @flg_id := (select flag_id from `t_privileges` where name='feereport' AND id = @priv_id LIMIT 1);
Insert into `t_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @product_id, @flg_id, @priv_id, '1');


SET @priv_id := (select id from `t_privileges` where name='agentsummary' LIMIT 1);
SET @flg_id := (select flag_id from `t_privileges` where name='agentsummary' AND id = @priv_id LIMIT 1);
Insert into `t_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @product_id, @flg_id, @priv_id, '1');

SET @priv_id := (select id from `t_privileges` where name='exportagentsummary' LIMIT 1);
SET @flg_id := (select flag_id from `t_privileges` where name='exportagentsummary' AND id = @priv_id LIMIT 1);
Insert into `t_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @product_id, @flg_id, @priv_id, '1');



SET @priv_id := (select id from `t_privileges` where name='agentcommissionsummary' LIMIT 1);
SET @flg_id := (select flag_id from `t_privileges` where name='agentcommissionsummary' AND id = @priv_id LIMIT 1);
Insert into `t_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @product_id, @flg_id, @priv_id, '1');

SET @priv_id := (select id from `t_privileges` where name='exportagentcommissionsummary' LIMIT 1);
SET @flg_id := (select flag_id from `t_privileges` where name='exportagentcommissionsummary' AND id = @priv_id LIMIT 1);
Insert into `t_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @product_id, @flg_id, @priv_id, '1');