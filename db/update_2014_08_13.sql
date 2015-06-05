INSERT INTO `t_transaction_type` (`typecode`, `name`, `status`, `date_created`, `is_comm`) VALUES ('CDRV', 'Card reversal ', 'active', CURRENT_TIMESTAMP, 'yes');
ALTER TABLE `rat_beneficiaries` ADD `txnrefnum` BIGINT(16) UNSIGNED NOT NULL AFTER `email`, ADD `txn_code` INT(11) UNSIGNED NULL AFTER `txnrefnum`;
ALTER TABLE `rat_beneficiaries` ADD `title` VARCHAR(10) NULL AFTER `remitter_id`;
ALTER TABLE `rat_beneficiaries` ADD `mobile2` VARCHAR(20) NULL AFTER `mobile`;
ALTER TABLE `rat_beneficiaries` ADD `landline` VARCHAR(20) NULL AFTER `mobile2`;
ALTER TABLE `rat_beneficiaries` ADD `first_name` VARCHAR(50) NULL AFTER `nick_name`, ADD `middle_name` VARCHAR(50) NULL AFTER `first_name`, ADD `last_name` VARCHAR(50) NULL AFTER `middle_name`, ADD `gender` VARCHAR(10) NULL AFTER `last_name`, ADD `date_of_birth` DATE NULL AFTER `gender`, ADD `mother_maiden_name` VARCHAR(25) NULL AFTER `date_of_birth`;
ALTER TABLE `rat_beneficiaries` ADD `city` VARCHAR(50) NULL AFTER `address_line2`, ADD `state` VARCHAR(50) NULL AFTER `city`, ADD `country` VARCHAR(5) NULL AFTER `state`, ADD `pincode` INT(6) NULL AFTER `country`;

ALTER TABLE `rat_beneficiaries` DROP `first_name`;


ALTER TABLE `rat_corp_log_cardholder` CHANGE `by_type` `by_type` ENUM('maker','checker','authorizer','ecs','system','api') CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL;

ALTER TABLE `rat_corp_cardholders` ADD `txnrefnum` BIGINT( 16 ) NOT NULL AFTER `partner_ref_no` ;


INSERT INTO `object_relation_types` (`id`, `label`, `description`) VALUES (NULL, 'RAT_MAPPER', 'RBL Mapper');
ALTER TABLE `rat_corp_cardholders` DROP `remitter_id` ;


INSERT INTO `api_user` (`id`, `tp_user_id`, `username`, `password`, `status`, `date_created`) VALUES (21, '21', 'payuuser', '3a45575180ac157510c80ac1a5244c4eba0762b3', 'active', '0000-00-00 00:00:00');
INSERT INTO `api_user_ip` (`id`, `tp_user_id`, `tp_user_ip`, `date_created`) VALUES (21, '21', '127.0.0.1,122.160.80.129,58.68.41.26,182.71.83.66,14.140.201.162', '0000-00-00 00:00:00');
