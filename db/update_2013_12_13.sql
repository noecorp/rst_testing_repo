SET @ops_id := 3;

SET @flag_id := (SELECT id FROM `t_flags` WHERE name = 'bank-corp_kotak_customer');

Insert into `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'view', @flag_id, 'Kotak Amul Customer detail view page');

SET @product_id := (SELECT id FROM `t_products` WHERE `unicode` = 510 AND status ='active');

SET @flag_id := (SELECT id FROM `t_flags` WHERE name = 'agent-corp_kotak_customer');

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'opsrejected', @flag_id, 'Customer registration complete');
SET @priv_id := (SELECT LAST_INSERT_ID());
INSERT INTO `t_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @product_id, @flag_id, @priv_id, '1');

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'index', @flag_id, 'Kotak Amul index page');
SET @priv_id := (SELECT LAST_INSERT_ID());
INSERT INTO `t_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @product_id, @flag_id, @priv_id, '1');

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'view', @flag_id, 'Kotak Amul customer detail view page');
SET @priv_id := (SELECT LAST_INSERT_ID());
INSERT INTO `t_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @product_id, @flag_id, @priv_id, '1');

