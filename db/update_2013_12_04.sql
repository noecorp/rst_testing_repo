SET @ops_id := 3;
SET @product_id := (SELECT id FROM `t_products` WHERE `unicode` = 510 AND status ='active');

SET @flag_id := (SELECT id FROM `t_flags` WHERE name = 'agent-corp_kotak_customer');

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'complete', @flag_id, 'Customer registration complete');
SET @priv_id := (SELECT LAST_INSERT_ID());
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');
INSERT INTO `t_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @product_id, @flag_id, @priv_id, '1');

