SET @product_id := (SELECT id FROM `t_products` WHERE name='Kotak Bank Shmart Transfer');

UPDATE `t_product_privileges` SET `flag_id` = '33' WHERE `t_product_privileges`.`privilege_id` = 225 AND `t_product_privileges`.`product_id` = @product_id;


SET @flag_id := (SELECT id FROM `t_flags` WHERE name='operation-remit_kotak_remitter' LIMIT 1);
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'holdtransactions', @flag_id, 'Process Hold Transactions');
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'processtransaction', @flag_id, 'Process Hold Transactions');
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'checkstatus', @flag_id, 'Process Hold Transactions');