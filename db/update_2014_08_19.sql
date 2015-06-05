INSERT INTO `object_relation_types` (`id`, `label`, `description`) VALUES (NULL, 'TYPE_J', 'Partner Ref Number');

UPDATE `t_products` SET `name` = 'PayUMoney Wallet' WHERE `t_products`.`unicode` = '916';


ALTER TABLE `rat_remit_remitters`
ADD COLUMN `rat_customer_id`  int(11) UNSIGNED NOT NULL AFTER `product_id`;

ALTER TABLE `rat_remittance_request`
ADD COLUMN `rat_customer_id`  int(11) UNSIGNED NOT NULL AFTER `id`,
ADD COLUMN `purse_master_id`  int(11) UNSIGNED NOT NULL AFTER `rat_customer_id`,
ADD COLUMN `customer_purse_id`  int(11) UNSIGNED NOT NULL AFTER `purse_master_id`;

