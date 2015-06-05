SET @product_id := (SELECT id FROM `t_products` WHERE `unicode` = 710 AND status ='active');

INSERT INTO `t_flags` (`name`, `description`, `active_on_dev`, `active_on_prod`) VALUES ('agent-corp_boi_reports', 'NSDC BOI Customers reports', '1', '0');
SET @flag_id_val = last_insert_id();

Insert into `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'customerregistration', @flag_id_val, 'Customer registration Details');
SET @privilege_id = last_insert_id();

Insert into `t_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @product_id, @flag_id_val, @privilege_id, '1');

Insert into `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'exportcustomerregistration', @flag_id_val, 'Export customerregistration');
SET @privilege_id = last_insert_id();

Insert into `t_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @product_id, @flag_id_val, @privilege_id, '1');

ALTER TABLE `boi_corp_cardholders` ADD `debit_mandate_accout` VARCHAR( 50 ) NOT NULL AFTER `ref_num`;
ALTER TABLE `boi_corp_cardholders` CHANGE `debit_mandate_accout` `debit_mandate_account` VARCHAR( 50 ) NOT NULL; 



ALTER TABLE `boi_corp_cardholders` ADD `debit_mandate_amount` INT( 11 ) NULL AFTER `ref_num`;
ALTER TABLE `boi_corp_cardholders` ADD `training_center_id` VARCHAR( 100 ) NULL AFTER `ref_num`;
ALTER TABLE `boi_corp_cardholders` ADD `traning_center_name` VARCHAR( 100 ) NULL AFTER `ref_num`;
ALTER TABLE `boi_corp_cardholders` ADD `training_partner_name` VARCHAR( 100 ) NOT NULL AFTER `ref_num`;