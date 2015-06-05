ALTER TABLE `delivery_file_master` ADD `batch_name` VARCHAR( 100 ) NOT NULL AFTER `delivery_status`;

ALTER TABLE `t_batch_adjustment`
ADD COLUMN `customer_master_id`  int(11) UNSIGNED NOT NULL AFTER `card_number`,
ADD COLUMN `cardholder_id`  int(11) UNSIGNED NOT NULL AFTER `customer_master_id`,
ADD COLUMN `purse_master_id`  int(11) UNSIGNED NOT NULL AFTER `cardholder_id`,
ADD COLUMN `customer_purse_id`  int(11) UNSIGNED NOT NULL AFTER `purse_master_id`;

ALTER TABLE `t_batch_adjustment`
ADD COLUMN `txn_type`  char(4) NOT NULL AFTER `customer_purse_id`;

ALTER TABLE `kotak_corp_cardholders`
ADD COLUMN `date_activation`  datetime NOT NULL AFTER `date_updated`;

