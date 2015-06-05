SET @ops_id := 3;


INSERT INTO `t_flags` VALUES (NULL, 'bank-filedownload', 'Manage download link for Customer docs', '1', '0');
SET @flag_id = last_insert_id();

Insert into `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'index', @flag_id, 'Manage download link for Customer docs');

SET @product_id := (SELECT id FROM `t_products` WHERE `unicode` = 510 AND status ='active');


INSERT INTO `t_flags` VALUES (NULL, 'agent-filedownload', 'Manage download link for Customer docs', '1', '0');
SET @flag_id = last_insert_id();

Insert into `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'index', @flag_id, 'Manage download link for Customer docs');
SET @priv_id = last_insert_id();
INSERT INTO `t_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @product_id, @flag_id, @priv_id, '1');

