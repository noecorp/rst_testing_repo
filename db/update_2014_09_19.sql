ALTER TABLE `kotak_remittance_request` ADD `otp` VARCHAR(20) NOT NULL AFTER `is_complete`, ADD `date_otp` DATETIME NULL AFTER `otp`; 

ALTER TABLE `rat_customer_master` ADD COLUMN `bank_id`  int(11) UNSIGNED NOT NULL AFTER `id`;
UPDATE rat_customer_master SET bank_id = 3;

ALTER TABLE `rat_customer_closing_balance` ADD COLUMN `bank_id`  int(11) UNSIGNED NOT NULL AFTER `id`;
UPDATE rat_customer_closing_balance SET bank_id = 3;

ALTER TABLE `rat_corp_cardholder_batch` ADD COLUMN `bank_id`  int(11) UNSIGNED NOT NULL AFTER `id`;
UPDATE rat_corp_cardholder_batch SET bank_id = 3;

ALTER TABLE `rat_corp_cardholders` ADD COLUMN `bank_id`  int(11) UNSIGNED NOT NULL AFTER `id`;
UPDATE rat_corp_cardholders SET bank_id = 3;

ALTER TABLE `rat_corp_cardholders_api_log` ADD COLUMN `bank_id`  int(11) UNSIGNED NOT NULL AFTER `id`;
UPDATE rat_corp_cardholders_api_log SET bank_id = 3;

ALTER TABLE `rat_corp_cardholders_details` ADD COLUMN `bank_id`  int(11) UNSIGNED NOT NULL AFTER `id`;
UPDATE rat_corp_cardholders_details SET bank_id = 3;

ALTER TABLE `rat_corp_load_request` ADD COLUMN `bank_id`  int(11) UNSIGNED NOT NULL AFTER `id`;
UPDATE rat_corp_load_request SET bank_id = 3;

ALTER TABLE `rat_corp_load_request_batch` ADD COLUMN `bank_id`  int(11) UNSIGNED NOT NULL AFTER `id`;
UPDATE rat_corp_load_request_batch SET bank_id = 3;

ALTER TABLE `rat_corp_load_request_detail` ADD COLUMN `bank_id`  int(11) UNSIGNED NOT NULL AFTER `id`;
UPDATE rat_corp_load_request_detail SET bank_id = 3;

ALTER TABLE `rat_remit_remitters` ADD COLUMN `bank_id`  int(11) UNSIGNED NOT NULL AFTER `id`;
UPDATE rat_remit_remitters SET bank_id = 3;

ALTER TABLE `rat_remittance_refund` ADD COLUMN `bank_id`  int(11) UNSIGNED NOT NULL AFTER `id`;
UPDATE rat_remittance_refund SET bank_id = 3;

ALTER TABLE `rat_remittance_request` ADD COLUMN `bank_id`  int(11) UNSIGNED NOT NULL AFTER `id`;
UPDATE rat_remittance_request SET bank_id = 3;

ALTER TABLE `rat_settlement_batch` ADD COLUMN `bank_id`  int(11) UNSIGNED NOT NULL AFTER `id`;
UPDATE rat_settlement_batch SET bank_id = 3;

ALTER TABLE `rat_txn_beneficiary` ADD COLUMN `bank_id`  int(11) UNSIGNED NOT NULL AFTER `id`;
UPDATE rat_txn_beneficiary SET bank_id = 3;

ALTER TABLE `rat_txn_customer` ADD COLUMN `bank_id`  int(11) UNSIGNED NOT NULL AFTER `id`;
UPDATE rat_txn_customer SET bank_id = 3;

ALTER TABLE `rat_txn_remitter`
ADD COLUMN `bank_id`  int(11) UNSIGNED NOT NULL AFTER `id`;
UPDATE rat_txn_remitter SET bank_id = 3;

ALTER TABLE `rat_update_corp_cardholders_log`
ADD COLUMN `bank_id`  int(11) UNSIGNED NOT NULL AFTER `id`;
UPDATE rat_update_corp_cardholders_log SET bank_id = 3;

ALTER TABLE `rat_wallet_transfer`
ADD COLUMN `bank_id`  int(11) UNSIGNED NOT NULL AFTER `id`;
UPDATE rat_wallet_transfer SET bank_id = 3;

ALTER TABLE `t_txn_agent`
ADD COLUMN `bank_id`  int(11) UNSIGNED NOT NULL AFTER `id`;

ALTER TABLE `t_txn_ops`
ADD COLUMN `bank_id`  int(11) UNSIGNED NOT NULL AFTER `id`;

ALTER TABLE `corporate_txn`
ADD COLUMN `bank_id`  int(11) UNSIGNED NOT NULL AFTER `id`;

ALTER TABLE `customer_track`
ADD COLUMN `bank_id`  int(11) UNSIGNED NOT NULL AFTER `id`;



SET @product_id := (SELECT id FROM `t_products` WHERE unicode='916');


INSERT INTO `t_product_privileges` (`product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (@product_id, '28', '146', 1);
INSERT INTO `t_product_privileges` (`product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (@product_id, '33', '173', 1);
INSERT INTO `t_product_privileges` (`product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (@product_id, '35', '193', 1);
INSERT INTO `t_product_privileges` (`product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (@product_id, '35', '194', 1);
INSERT INTO `t_product_privileges` (`product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (@product_id, '33', '203', 1);
INSERT INTO `t_product_privileges` (`product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (@product_id, '32', '208', 1);
INSERT INTO `t_product_privileges` (`product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (@product_id, '32', '209', 1);
INSERT INTO `t_product_privileges` (`product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (@product_id, '32', '210', 1);
INSERT INTO `t_product_privileges` (`product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (@product_id, '33', '224', 1);
INSERT INTO `t_product_privileges` (`product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (@product_id, '33', '225', 1);
INSERT INTO `t_product_privileges` (`product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (@product_id, '33', '228', 1);
INSERT INTO `t_product_privileges` (`product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (@product_id, '33', '239', 1);
INSERT INTO `t_product_privileges` (`product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (@product_id, '33', '240', 1);
INSERT INTO `t_product_privileges` (`product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (@product_id, '33', '253', 1);
INSERT INTO `t_product_privileges` (`product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (@product_id, '33', '254', 1);
INSERT INTO `t_product_privileges` (`product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (@product_id, '33', '433', 1);
INSERT INTO `t_product_privileges` (`product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (@product_id, '33', '434', 1);


INSERT INTO `t_product_privileges` VALUES (NULL, @product_id, '64', '345', '1');
INSERT INTO `t_product_privileges` VALUES (NULL, @product_id, '64', '346', '1');
INSERT INTO `t_product_privileges` VALUES (NULL, @product_id, '64', '351', '1');
INSERT INTO `t_product_privileges` VALUES (NULL, @product_id, '64', '353', '1');



SET @product_id := (SELECT id FROM `t_products` WHERE unicode='917');


INSERT INTO `t_product_privileges` (`product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (@product_id, '28', '146', 1);
INSERT INTO `t_product_privileges` (`product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (@product_id, '33', '173', 1);
INSERT INTO `t_product_privileges` (`product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (@product_id, '35', '193', 1);
INSERT INTO `t_product_privileges` (`product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (@product_id, '35', '194', 1);
INSERT INTO `t_product_privileges` (`product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (@product_id, '33', '203', 1);
INSERT INTO `t_product_privileges` (`product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (@product_id, '32', '208', 1);
INSERT INTO `t_product_privileges` (`product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (@product_id, '32', '209', 1);
INSERT INTO `t_product_privileges` (`product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (@product_id, '32', '210', 1);
INSERT INTO `t_product_privileges` (`product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (@product_id, '33', '224', 1);
INSERT INTO `t_product_privileges` (`product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (@product_id, '33', '225', 1);
INSERT INTO `t_product_privileges` (`product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (@product_id, '33', '228', 1);
INSERT INTO `t_product_privileges` (`product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (@product_id, '33', '239', 1);
INSERT INTO `t_product_privileges` (`product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (@product_id, '33', '240', 1);
INSERT INTO `t_product_privileges` (`product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (@product_id, '33', '253', 1);
INSERT INTO `t_product_privileges` (`product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (@product_id, '33', '254', 1);
INSERT INTO `t_product_privileges` (`product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (@product_id, '33', '433', 1);
INSERT INTO `t_product_privileges` (`product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (@product_id, '33', '434', 1);


INSERT INTO `t_product_privileges` VALUES (NULL, @product_id, '64', '345', '1');
INSERT INTO `t_product_privileges` VALUES (NULL, @product_id, '64', '346', '1');
INSERT INTO `t_product_privileges` VALUES (NULL, @product_id, '64', '351', '1');
INSERT INTO `t_product_privileges` VALUES (NULL, @product_id, '64', '353', '1');

