ALTER TABLE `boi_corp_cardholders`
ADD COLUMN `customer_type`  enum('kyc','non-kyc') NULL AFTER `customer_master_id`;

INSERT INTO `shmart`.`t_fund_transfer_type` (`id`, `name`, `status`, `by_ops_id`, `date_created`) VALUES (NULL, 'Bank Transfer', 'active', '101', CURRENT_TIMESTAMP);
ALTER TABLE `rat_update_corp_cardholders_log` ADD `product_customer_id` INT NOT NULL AFTER `id` ;
ALTER TABLE `rat_wallet_transfer` ADD `narration` VARCHAR(40) NOT NULL AFTER `amount`, ADD `txnrefnum` VARCHAR(20) NOT NULL AFTER `narration`;
ALTER TABLE `rat_wallet_transfer` ADD `product_id` INT(11) NOT NULL AFTER `id`;

ALTER TABLE `rat_remittance_refund` ADD COLUMN `rat_customer_id` int(11) UNSIGNED NOT NULL AFTER `id`, ADD COLUMN `purse_master_id` int(11) UNSIGNED NOT NULL AFTER `rat_customer_id`, ADD COLUMN `customer_purse_id` int(11) UNSIGNED NOT NULL AFTER `purse_master_id` 
