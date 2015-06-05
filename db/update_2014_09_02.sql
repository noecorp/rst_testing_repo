SET @group_id := 3;
SET @product_id := (SELECT id FROM `t_products` WHERE unicode='914');
SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-reports'); 

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'remitwallettrialbalance', @flag_id, 'Remittance Wallet Trial Balance Report');
SET @priv_id = last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @group_id, @flag_id, @priv_id, '1');

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'exportremitwallettrialbalance', @flag_id, 'Export Remittance Wallet Trial Balance Report');
SET @priv_id = last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @group_id, @flag_id, @priv_id, '1');
