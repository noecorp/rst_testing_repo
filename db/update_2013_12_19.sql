SET @ops_id := 3;

SET @product_id := (SELECT id FROM `t_products` WHERE `unicode` = 510 AND status ='active');

SET @flag_id = (SELECT id FROM `t_flags` where `name` = 'agent-corp_kotak_customer');

Insert into `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'edit', @flag_id, 'Edit and Resubmit the ops rejected application');
SET @priv_id = last_insert_id();
INSERT INTO `t_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @product_id, @flag_id, @priv_id, '1');

Insert into `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'editcomplete', @flag_id, 'Edit and Resubmit the ops rejected application complete');
SET @priv_id = last_insert_id();
INSERT INTO `t_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @product_id, @flag_id, @priv_id, '1');

