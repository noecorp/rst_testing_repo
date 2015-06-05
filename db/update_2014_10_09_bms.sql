INSERT INTO `api_user` (`id`, `tp_user_id`, `username`, `password`, `status`, `date_created`) VALUES ('27', '27', 'bookmyshow', '34rf3a4557510c82d47d0ac1a5244c4eba0762b3', 'active', '0000-00-00 00:00:00');

INSERT INTO `api_user_ip` (`id`, `tp_user_id`, `tp_user_ip`, `date_created`) VALUES (NULL, '27', '127.0.0.1,122.160.80.129,58.68.41.26,182.71.83.66,14.140.201.162', '0000-00-00 00:00:00');


INSERT INTO `t_products` (`id`, `bank_id`, `name`, `description`, `currency`, `ecs_product_code`, `program_type`, `unicode`, `const`, `flag_common`, `static_otp`, `by_ops_id`, `ip`, `date_created`, `status`) VALUES ('23', '3', 'RATNAKAR BOOK MY SHOW', 'RATNAKAR BOOK MY SHOW', 'INR', '10000045', 'DigiWallet', '923', 'RAT_BOOKMYSHOW', 'yes', 'no', '101', '127000000001', '2014-09-09 16:07:36', 'active');

INSERT INTO `purse_master` (`id`, `bank_id`, `product_id`, `global_purse_id`, `code`, `name`, `description`, `max_balance`, `allow_remit`, `allow_mvc`, `load_channel`, `load_validity_day`, `load_validity_hr`, `load_validity_min`, `load_min`, `load_max`, `load_max_cnt_daily`, `load_max_val_daily`, `load_max_cnt_monthly`, `load_max_val_monthly`, `load_max_cnt_yearly`, `load_max_val_yearly`, `txn_restriction_type`, `txn_upload_list`, `txn_min`, `txn_max`, `txn_max_cnt_daily`, `txn_max_val_daily`, `txn_max_cnt_monthly`, `txn_max_val_monthly`, `txn_max_cnt_yearly`, `txn_max_val_yearly`, `priority`, `date_start`, `date_created`, `date_updated`, `by_ops_id`, `status`) VALUES (NULL, '3', '23', '1', 'BMS923', 'Ratnakar BMS Wallet', 'Ratnakar BMS Wallet', '50000', 'yes', 'no', 'api', '0', '0', '0', '750', '50000', '0', '0', '0', '200000', '0', '0', 'mcc', 'yes', '1', '10000', '0', '0', '0', '200000', '0', '0', '1', '2014-09-09 15:15:32', '2014-09-09 15:15:32', '2014-09-09 15:15:32', '101', 'active');

INSERT INTO `product_customer_limits` VALUES (NULL, '3', '23', 'KYC923', 'kyc', 'KYC Ratnakar Book My Show', 'KYC Ratnakar Book My Show', '10000', '1', '10000', '10000', '10000', '120000', '1', '10000', '10000', '10000', '120000', NOW(), NOW(), NOW(), '101', 'active');

INSERT INTO `product_customer_limits` VALUES (NULL, '3', '23', 'NKC923', 'non-kyc', 'Non-KYC Ratnakar Book My Show', 'Non KYC Ratnakar Book My Show', '10000', '1', '10000', '10000', '10000', '120000', '1', '10000', '10000', '10000', '120000', NOW(), NOW(), NOW(), '101', 'active');

INSERT INTO `api_user` (`id`, `tp_user_id`, `username`, `password`, `status`, `date_created`) VALUES ('26', '26', 'hfci', '34rf3a4557510c82d47d0ac1a5244c4eba0762b3', 'active', '0000-00-00 00:00:00');

INSERT INTO `api_user_ip` (`id`, `tp_user_id`, `tp_user_ip`, `date_created`) VALUES (NULL, '26', '127.0.0.1,122.160.80.129,58.68.41.26,182.71.83.66,14.140.201.162', '0000-00-00 00:00:00');
