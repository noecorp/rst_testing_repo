ALTER TABLE `t_agents` ADD `bcagent` VARCHAR(15) NULL DEFAULT NULL ;
ALTER TABLE `rat_remit_remitters` ADD `remitterid` VARCHAR(10) NULL DEFAULT NULL ;
ALTER TABLE `rat_beneficiaries` ADD `beneficiary_id` INT(10) NULL ;
ALTER TABLE `rat_beneficiaries` ADD COLUMN `rat_status` INTEGER UNSIGNED NOT NULL DEFAULT 0;
ALTER TABLE `rat_remittance_request` ADD COLUMN `rbl_transaction_id` VARCHAR(20) DEFAULT NULL AFTER `settlement_remarks`;

ALTER TABLE `rat_remittance_request` ADD COLUMN `flag` INTEGER UNSIGNED NOT NULL DEFAULT 0 AFTER `rbl_transaction_id`;

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'requery', '115', 'Ratnakar remitter transaction Re-query ');

INSERT INTO `t_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, '16', '115', '898', '1');