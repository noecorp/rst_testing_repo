	
SET @flag_id := (SELECT id FROM `t_flags` WHERE name = 'agent-reports');
SET @product_id := (SELECT id FROM `t_products` WHERE `unicode` = 410 AND status ='active');
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'balancesheet', @flag_id, 'Agent Balance Sheet report for agent');
SET @priv_id := (SELECT LAST_INSERT_ID());
INSERT INTO `t_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @product_id, @flag_id, @priv_id, '1');

SET @flag_id := (SELECT id FROM `t_flags` WHERE name = 'agent-reports');
SET @product_id := (SELECT id FROM `t_products` WHERE `unicode` = 410 AND status ='active');
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'exportbalancesheet', @flag_id, 'Export Agent Balance Sheet report for agent');
SET @priv_id := (SELECT LAST_INSERT_ID());
INSERT INTO `t_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @product_id, @flag_id, @priv_id, '1');

SET @flag_id := (SELECT id FROM `t_flags` WHERE name = 'agent-reports');
SET @product_id := (SELECT id FROM `t_products` WHERE `unicode` = 410 AND status ='active');
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'exportdailytxn', @flag_id, 'Export agent daily transactions report for agent');
SET @priv_id := (SELECT LAST_INSERT_ID());
INSERT INTO `t_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @product_id, @flag_id, @priv_id, '1');

