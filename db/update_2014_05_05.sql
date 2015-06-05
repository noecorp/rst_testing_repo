DROP TABLE IF EXISTS `bind_global_purse_mcc`;
CREATE TABLE `bind_global_purse_mcc` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `global_purse_id` int(11) unsigned NOT NULL,
  `mcc_code` varchar(10) NOT NULL,
  `datetime_start` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `date_created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `date_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `by_ops_id` int(11) unsigned NOT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



DROP TABLE IF EXISTS `global_purse_master`;
CREATE TABLE `global_purse_master` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` varchar(255) NOT NULL,
  `max_balance` int(11) unsigned NOT NULL,
  `allow_remit` enum('yes','no') NOT NULL DEFAULT 'no',
  `allow_mvc` enum('yes','no') NOT NULL DEFAULT 'no',
  `load_validity_day` tinyint(4) unsigned NOT NULL,
  `load_validity_hr` tinyint(4) unsigned NOT NULL,
  `load_validity_min` tinyint(4) unsigned NOT NULL,
  `load_min` int(11) unsigned NOT NULL,
  `load_max` int(11) unsigned NOT NULL,
  `load_max_cnt_daily` int(11) unsigned NOT NULL,
  `load_max_val_daily` int(11) unsigned NOT NULL,
  `load_max_cnt_monthly` int(11) unsigned NOT NULL,
  `load_max_val_monthly` int(11) unsigned NOT NULL,
  `load_max_cnt_yearly` int(11) unsigned NOT NULL,
  `load_max_val_yearly` int(11) unsigned NOT NULL,
  `txn_restriction_type` enum('mcc','tid','none') DEFAULT NULL,
  `txn_upload_list` enum('yes','no') NOT NULL DEFAULT 'no',
  `txn_min` int(11) unsigned NOT NULL,
  `txn_max` int(11) unsigned NOT NULL,
  `txn_max_cnt_daily` int(11) unsigned NOT NULL,
  `txn_max_val_daily` int(11) unsigned NOT NULL,
  `txn_max_cnt_monthly` int(11) unsigned NOT NULL,
  `txn_max_val_monthly` int(11) unsigned NOT NULL,
  `txn_max_cnt_yearly` int(11) unsigned NOT NULL,
  `txn_max_val_yearly` int(11) unsigned NOT NULL,
  `date_start` date NOT NULL,
  `date_created` timestamp NULL DEFAULT NULL,
  `date_updated` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `by_ops_id` int(11) unsigned NOT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=latin1;


INSERT INTO `global_purse_master` VALUES ('1', 'General Wallet', 'General Wallet (except ATM)', '10000000', 'no', 'no', '0', '0', '0', '1', '1000000', '0', '0', '0', '0', '0', '0', 'mcc', 'no', '1', '100000', '0', '0', '0', '0', '0', '0', '2014-05-05', '2014-05-05 13:59:58', '2014-05-05 13:59:58', '100', 'active');
INSERT INTO `global_purse_master` VALUES ('2', 'Fuel Wallet', 'Fuel Wallet', '1000000', 'no', 'no', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', 'mcc', 'yes', '0', '0', '0', '0', '0', '0', '0', '0', '2014-05-05', '2014-05-05 13:59:58', '2014-05-05 14:10:57', '100', 'active');
INSERT INTO `global_purse_master` VALUES ('3', 'Medical Wallet', 'Medical Wallet', '1000000', 'no', 'no', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', 'mcc', 'yes', '0', '0', '0', '0', '0', '0', '0', '0', '2014-05-05', '2014-05-05 13:59:58', '2014-05-05 14:10:58', '100', 'active');
INSERT INTO `global_purse_master` VALUES ('4', 'Meal Wallet', 'Meal Wallet', '1000000', 'no', 'no', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', 'mcc', 'yes', '0', '0', '0', '0', '0', '0', '0', '0', '2014-05-05', '2014-05-05 13:59:58', '2014-05-05 14:10:58', '100', 'active');
INSERT INTO `global_purse_master` VALUES ('5', 'Travel Wallet', 'Travel Wallet', '1000000', 'no', 'no', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', 'mcc', 'yes', '0', '0', '0', '0', '0', '0', '0', '0', '2014-05-05', '2014-05-05 13:59:58', '2014-05-05 14:10:59', '100', 'active');
INSERT INTO `global_purse_master` VALUES ('6', 'Cashless', 'Cashless Wallet', '1000000', 'no', 'no', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', 'none', 'no', '0', '0', '0', '0', '0', '0', '0', '0', '2014-05-05', '2014-05-05 13:59:58', '2014-05-05 14:11:00', '100', 'active');


DROP TABLE IF EXISTS `log_bind_global_purse_mcc`;
CREATE TABLE `log_bind_global_purse_mcc` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `bind_id` int(11) unsigned NOT NULL,
  `global_purse_id` int(11) unsigned NOT NULL,
  `mcc_code` varchar(10) NOT NULL,
  `datetime_start` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `datetime_end` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `date_updated` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `by_ops_id` int(11) unsigned NOT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS `log_global_purse_master`;
CREATE TABLE `log_global_purse_master` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `global_purse_id` int(11) unsigned NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` varchar(255) NOT NULL,
  `max_balance` int(11) unsigned NOT NULL,
  `allow_remit` enum('yes','no') NOT NULL DEFAULT 'no',
  `allow_mvc` enum('yes','no') NOT NULL DEFAULT 'no',
  `load_validity_day` tinyint(4) unsigned NOT NULL,
  `load_validity_hr` tinyint(4) unsigned NOT NULL,
  `load_validity_min` tinyint(4) unsigned NOT NULL,
  `load_min` int(11) unsigned NOT NULL,
  `load_max` int(11) unsigned NOT NULL,
  `load_max_cnt_daily` int(11) unsigned NOT NULL,
  `load_max_val_daily` int(11) unsigned NOT NULL,
  `load_max_cnt_monthly` int(11) unsigned NOT NULL,
  `load_max_val_monthly` int(11) unsigned NOT NULL,
  `load_max_cnt_yearly` int(11) unsigned NOT NULL,
  `load_max_val_yearly` int(11) unsigned NOT NULL,
  `txn_restriction_type` enum('mcc','tid','none') DEFAULT NULL,
  `txn_upload_list` enum('yes','no') NOT NULL DEFAULT 'no',
  `txn_min` int(11) unsigned NOT NULL,
  `txn_max` int(11) unsigned NOT NULL,
  `txn_max_cnt_daily` int(11) unsigned NOT NULL,
  `txn_max_val_daily` int(11) unsigned NOT NULL,
  `txn_max_cnt_monthly` int(11) unsigned NOT NULL,
  `txn_max_val_monthly` int(11) unsigned NOT NULL,
  `txn_max_cnt_yearly` int(11) unsigned NOT NULL,
  `txn_max_val_yearly` int(11) unsigned NOT NULL,
  `date_start` date NOT NULL,
  `date_end` date NOT NULL,
  `date_updated` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `by_ops_id` int(11) unsigned NOT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


ALTER TABLE `global_purse_master`
CHANGE COLUMN `date_start` `datetime_start`  timestamp NULL AFTER `txn_max_val_yearly`;

ALTER TABLE `log_global_purse_master`
CHANGE COLUMN `date_start` `datetime_start`  timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' AFTER `txn_max_val_yearly`,
CHANGE COLUMN `date_end` `datetime_end`  timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' AFTER `datetime_start`;



ALTER TABLE `log_product_customer_limits`
MODIFY COLUMN `date_start`  timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' AFTER `txn_max_val_yearly`,
MODIFY COLUMN `date_end`  timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' AFTER `date_start`;

ALTER TABLE `product_customer_limits`
MODIFY COLUMN `date_start`  timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' AFTER `txn_max_val_yearly`;

ALTER TABLE `purse_master`
MODIFY COLUMN `date_start`  timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' AFTER `priority`;

ALTER TABLE `log_purse_master`
MODIFY COLUMN `date_start`  timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' AFTER `txn_max_val_yearly`,
MODIFY COLUMN `date_end`  timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' AFTER `date_start`;

UPDATE purse_master SET global_purse_id = 1;