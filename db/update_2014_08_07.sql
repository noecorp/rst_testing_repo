DROP TABLE IF EXISTS `rat_response_file_status_log`;
CREATE TABLE IF NOT EXISTS `rat_response_file_status_log` (
  `response_file_id` int(11) NOT NULL,
  `status_old` enum('process','reject','refund','processed') NULL,
  `status_new` enum('process','reject','refund','processed') NULL,
  `rejection_code` varchar(15) NOT NULL,
  `rejection_remark` varchar(30) NOT NULL,
  `description` varchar(100) NOT NULL,
  `by_remitter_id` int(11) NOT NULL,
  `by_agent_id` int(11) NOT NULL,
  `by_ops_id` int(11) unsigned NOT NULL,
  `date_created` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


INSERT INTO `t_transaction_type` (`typecode`, `name`, `status`, `date_created`, `is_comm`) VALUES ('RMSF', 'Remittance Success to Failure', 'active', '2013-08-07 19:38:07', 'no');

ALTER TABLE `rat_response_file_status_log` CHANGE `rejection_code` `rejection_code` VARCHAR(15) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL, CHANGE `rejection_remark` `rejection_remark` VARCHAR(30) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL;

INSERT INTO `shmart`.`api_user` (`id`, `tp_user_id`, `username`, `password`, `status`, `date_created`) VALUES (NULL, '19', 'ktkgpruser', '6b5c3a4557510c82d47d0ac1a5244c4eba0762b3', 'active', '0000-00-00 00:00:00');
INSERT INTO `api_user` (`id`, `tp_user_id`, `username`, `password`, `status`, `date_created`) VALUES (NULL, '19', 'ktkgpruser', '6b5c3a4557510c82d47d0ac1a5244c4eba0762b3', 'active', '0000-00-00 00:00:00');

INSERT INTO `api_user` (`id`, `tp_user_id`, `username`, `password`, `status`, `date_created`) VALUES (NULL, '20', 'ratcnyuser', '6b5c3a4557510c82d47d0ac1a5244c4eba0762b3', 'active', '0000-00-00 00:00:00');

INSERT INTO `shmart`.`api_user_ip` (`id`, `tp_user_id`, `tp_user_ip`, `date_created`) VALUES (NULL, '19', '127.0.0.1,122.160.80.129,58.68.41.26,182.71.83.66,14.140.201.162', '0000-00-00 00:00:00');
INSERT INTO `shmart`.`api_user_ip` (`id`, `tp_user_id`, `tp_user_ip`, `date_created`) VALUES (NULL, '20', '127.0.0.1,122.160.80.129,58.68.41.26,182.71.83.66,14.140.201.162', '0000-00-00 00:00:00');
INSERT INTO `api_user_ip` (`id`, `tp_user_id`, `tp_user_ip`, `date_created`) VALUES (NULL, '19', '127.0.0.1,122.160.80.129,58.68.41.26,182.71.83.66,14.140.201.162', '0000-00-00 00:00:00');
INSERT INTO `api_user_ip` (`id`, `tp_user_id`, `tp_user_ip`, `date_created`) VALUES (NULL, '20', '127.0.0.1,122.160.80.129,58.68.41.26,182.71.83.66,14.140.201.162', '0000-00-00 00:00:00');



INSERT INTO `t_products` (`id`, `bank_id`, `name`, `description`, `currency`, `ecs_product_code`, `program_type`, `unicode`, `const`, `flag_common`, `static_otp`, `by_ops_id`, `ip`, `date_created`, `status`) VALUES ('18', '3', 'RATNAKAR PAY U', 'RATNAKAR PAY U', 'INR', '10000044', 'DigiWallet', '916', 'RAT_PAYU', 'yes', 'no', '101', '127000000001', '2014-08-07 16:07:36', 'active');

INSERT INTO `purse_master` (`id`, `bank_id`, `product_id`, `global_purse_id`, `code`, `name`, `description`, `max_balance`, `allow_remit`, `allow_mvc`, `load_channel`, `load_validity_day`, `load_validity_hr`, `load_validity_min`, `load_min`, `load_max`, `load_max_cnt_daily`, `load_max_val_daily`, `load_max_cnt_monthly`, `load_max_val_monthly`, `load_max_cnt_yearly`, `load_max_val_yearly`, `txn_restriction_type`, `txn_upload_list`, `txn_min`, `txn_max`, `txn_max_cnt_daily`, `txn_max_val_daily`, `txn_max_cnt_monthly`, `txn_max_val_monthly`, `txn_max_cnt_yearly`, `txn_max_val_yearly`, `priority`, `date_start`, `date_created`, `date_updated`, `by_ops_id`, `status`) VALUES (NULL, '3', '18', '1', 'PAY916', 'Ratnakar Payu Wallet', 'Ratnakar Payu Wallet', '50000', 'yes', 'no', 'api', '0', '0', '0', '750', '50000', '0', '0', '0', '200000', '0', '0', 'mcc', 'yes', '1', '10000', '0', '0', '0', '200000', '0', '0', '1', '2014-08-08 15:15:32', '2014-08-08 15:15:32', '2014-08-08 15:15:32', '101', 'active');

INSERT INTO `product_customer_limits` VALUES (NULL, '1', '18', 'KYC916', 'kyc', 'KYC Ratnakar Payu', 'KYC Ratnakar Payu', '10000', '1', '10000', '10000', '10000', '120000', '1', '10000', '10000', '10000', '120000', NOW(), NOW(), NOW(), '101', 'active');

INSERT INTO `product_customer_limits` VALUES (NULL, '1', '18', 'NKC916', 'non-kyc', 'Non-KYC Ratnakar Payu', 'Non KYC Ratnakar Payu', '10000', '1', '10000', '10000', '10000', '120000', '1', '10000', '10000', '10000', '120000', NOW(), NOW(), NOW(), '101', 'active');



INSERT INTO `t_products` (`id`, `bank_id`, `name`, `description`, `currency`, `ecs_product_code`, `program_type`, `unicode`, `const`, `flag_common`, `static_otp`, `by_ops_id`, `ip`, `date_created`, `status`) VALUES ('19', '3', 'RATNAKAR SHOPCLUES', 'RATNAKAR SHOPCLUES', 'INR', '10000045', 'DigiWallet', '917', 'RAT_SHOPCLUES', 'yes', 'no', '101', '127000000001', '2014-08-07 16:07:36', 'active');

INSERT INTO `purse_master` (`id`, `bank_id`, `product_id`, `global_purse_id`, `code`, `name`, `description`, `max_balance`, `allow_remit`, `allow_mvc`, `load_channel`, `load_validity_day`, `load_validity_hr`, `load_validity_min`, `load_min`, `load_max`, `load_max_cnt_daily`, `load_max_val_daily`, `load_max_cnt_monthly`, `load_max_val_monthly`, `load_max_cnt_yearly`, `load_max_val_yearly`, `txn_restriction_type`, `txn_upload_list`, `txn_min`, `txn_max`, `txn_max_cnt_daily`, `txn_max_val_daily`, `txn_max_cnt_monthly`, `txn_max_val_monthly`, `txn_max_cnt_yearly`, `txn_max_val_yearly`, `priority`, `date_start`, `date_created`, `date_updated`, `by_ops_id`, `status`) VALUES (NULL, '3', '19', '1', 'SHO917', 'Ratnakar Shopclues Wallet', 'Ratnakar Shopclues Wallet', '50000', 'yes', 'no', 'api', '0', '0', '0', '750', '50000', '0', '0', '0', '200000', '0', '0', 'mcc', 'yes', '1', '10000', '0', '0', '0', '200000', '0', '0', '1', '2014-08-08 15:15:32', '2014-08-08 15:15:32', '2014-08-08 15:15:32', '101', 'active');

INSERT INTO `product_customer_limits` VALUES (NULL, '1', '19', 'KYC917', 'kyc', 'KYC Ratnakar Shopclues', 'KYC Ratnakar Shopclues', '10000', '1', '10000', '10000', '10000', '120000', '1', '10000', '10000', '10000', '120000', NOW(), NOW(), NOW(), '101', 'active');

INSERT INTO `product_customer_limits` VALUES (NULL, '1', '19', 'NKC917', 'non-kyc', 'Non-KYC Ratnakar Shopclues', 'Non KYC Ratnakar Shopclues', '10000', '1', '10000', '10000', '10000', '120000', '1', '10000', '10000', '10000', '120000', NOW(), NOW(), NOW(), '101', 'active');


ALTER TABLE `rat_corp_cardholders` ADD `remitter_id` INT(11) UNSIGNED NOT NULL AFTER `customer_type`;
