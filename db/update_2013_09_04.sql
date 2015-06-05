ALTER TABLE `kotak_remittance_request` CHANGE `status` `status` ENUM( 'in_process', 'success', 'failure', 'incomplete', 'hold', 'refund', 'fail_on_hold' ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT 'in_process';
ALTER TABLE `kotak_remittance_request` ADD `hold_reason` TEXT NOT NULL AFTER `status`;

ALTER TABLE `kotak_txn_remitter`
ADD COLUMN `date_updated`  timestamp NULL DEFAULT NULL AFTER `date_created`;

ALTER TABLE `t_txn_ops`
ADD COLUMN `date_updated`  timestamp NULL DEFAULT NULL AFTER `date_created`;

ALTER TABLE `t_txn_ops`
ADD COLUMN `kotak_beneficiary_id`  int(11) UNSIGNED NULL AFTER `txn_beneficiary_id`;

SET @product_id := (SELECT id FROM `t_products` WHERE name='Kotak Bank Shmart Transfer');

INSERT INTO `t_product_privileges` (`product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (@product_id, '28', '146', 1);
INSERT INTO `t_product_privileges` (`product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (@product_id, '33', '173', 1);
INSERT INTO `t_product_privileges` (`product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (@product_id, '35', '194', 1);
INSERT INTO `t_product_privileges` (`product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (@product_id, '32', '208', 1);
INSERT INTO `t_product_privileges` (`product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (@product_id, '32', '210', 1);
INSERT INTO `t_product_privileges` (`product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (@product_id, '35', '193', 1);
INSERT INTO `t_product_privileges` (`product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (@product_id, '33', '203', 1);
INSERT INTO `t_product_privileges` (`product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (@product_id, '32', '209', 1);


ALTER TABLE `kotak_beneficiaries`
MODIFY COLUMN `mobile`  varchar(20) NOT NULL DEFAULT '0' AFTER `address_line2`;

ALTER TABLE `kotak_beneficiaries`
MODIFY COLUMN `email`  varchar(60) NULL DEFAULT NULL AFTER `mobile`;


UPDATE t_bank SET logo = 'logo-kotak.jpg' WHERE name = 'KOTAK MAHINDRA BANK';

