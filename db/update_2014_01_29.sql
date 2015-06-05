SET @product_id := (SELECT id FROM `t_products` WHERE `unicode` = 710 AND status ='active');

SET @flag_id_val := (SELECT id FROM `t_flags` where name ='agent-reports');

Insert into `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'bclisting', @flag_id_val, 'Agents under distributor Listing');
SET @privilege_id = last_insert_id();

Insert into `t_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @product_id, @flag_id_val, @privilege_id, '1');

Insert into `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'exportbclisting', @flag_id_val, 'Export Agents under distributor Listing');
SET @privilege_id = last_insert_id();

Insert into `t_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @product_id, @flag_id_val, @privilege_id, '1');

SET @product_id := (SELECT id FROM `t_products` WHERE `unicode` = 710 AND status ='active');

SET @flag_id_val := (SELECT id FROM `t_flags` where name ='agent-profile');

Insert into `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'sendotp', @flag_id_val, 'Send OTP for edit bank details');
SET @privilege_id = last_insert_id();

Insert into `t_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @product_id, @flag_id_val, @privilege_id, '1');

Insert into `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'verification', @flag_id_val, 'OTP verification screen');
SET @privilege_id = last_insert_id();

Insert into `t_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @product_id, @flag_id_val, @privilege_id, '1');

Insert into `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'editbank', @flag_id_val, 'Edit Bank Details');
SET @privilege_id = last_insert_id();

Insert into `t_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @product_id, @flag_id_val, @privilege_id, '1');
