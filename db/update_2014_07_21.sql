INSERT INTO `t_products` (`id`, `bank_id`, `name`, `description`, `currency`, `ecs_product_code`, `program_type`, `unicode`, `const`, `flag_common`, `static_otp`, `by_ops_id`, `ip`, `date_created`, `status`) VALUES ('17', '3', 'RATNAKAR SURYODAY', 'RATNAKAR SURYODAY', 'INR', '10000043', 'Corp', '915', 'RAT_SURYODAY', 'yes', 'no', '101', '127000000001', '2014-07-21 16:07:36', 'active');

INSERT INTO `purse_master` (`id`, `bank_id`, `product_id`, `global_purse_id`, `code`, `name`, `description`, `max_balance`, `allow_remit`, `allow_mvc`, `load_channel`, `load_validity_day`, `load_validity_hr`, `load_validity_min`, `load_min`, `load_max`, `load_max_cnt_daily`, `load_max_val_daily`, `load_max_cnt_monthly`, `load_max_val_monthly`, `load_max_cnt_yearly`, `load_max_val_yearly`, `txn_restriction_type`, `txn_upload_list`, `txn_min`, `txn_max`, `txn_max_cnt_daily`, `txn_max_val_daily`, `txn_max_cnt_monthly`, `txn_max_val_monthly`, `txn_max_cnt_yearly`, `txn_max_val_yearly`, `priority`, `date_start`, `date_created`, `date_updated`, `by_ops_id`, `status`) VALUES (NULL, '3', '17', '1', 'SUR915', 'Ratnakar Suryoday Wallet', 'Ratnakar Suryoday Wallet', '50000', 'no', 'no', 'api', '0', '0', '0', '750', '50000', '0', '0', '0', '200000', '0', '0', 'mcc', 'yes', '1', '10000', '0', '0', '0', '200000', '0', '0', '1', '2014-07-21 15:15:32', '2014-07-21 15:15:32', '2014-07-21 15:15:32', '101', 'active');


INSERT INTO `product_customer_limits` (`id`, `bank_id`, `product_id`, `code`, `customer_type`, `name`, `description`, `max_balance`, `load_min`, `load_max`, `load_max_val_daily`, `load_max_val_monthly`, `load_max_val_yearly`, `txn_min`, `txn_max`, `txn_max_val_daily`, `txn_max_val_monthly`, `txn_max_val_yearly`, `date_start`, `date_created`, `date_updated`, `by_ops_id`, `status`) VALUES (NULL, '3', '17', 'KYC915', 'kyc', 'KYC Ratnakar Suryoday GPR', 'KYC Ratnakar Suryoday GPR', '50000', '0', '50000', '50000', '200000', '0', '1', '50000', '50000', '200000', '0', '2014-07-21 12:01:18', '2014-07-21 12:01:18', '2014-07-21 12:01:18', '101', 'active');


INSERT INTO `product_customer_limits` (`id`, `bank_id`, `product_id`, `code`, `customer_type`, `name`, `description`, `max_balance`, `load_min`, `load_max`, `load_max_val_daily`, `load_max_val_monthly`, `load_max_val_yearly`, `txn_min`, `txn_max`, `txn_max_val_daily`, `txn_max_val_monthly`, `txn_max_val_yearly`, `date_start`, `date_created`, `date_updated`, `by_ops_id`, `status`) VALUES (NULL, '3', '17', 'NKC915', 'non-kyc', 'Non KYC Ratnakar Suryoday GPR', 'Non KYC Ratnakar Suryoday GPR', '10000', '0', '10000', '10000', '200000', '0', '1', '10000', '10000', '200000', '0', '2014-07-21 12:01:18', '2014-07-21 12:01:18', '2014-07-21 12:01:18', '101', 'active');
