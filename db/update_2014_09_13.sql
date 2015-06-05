CREATE TABLE IF NOT EXISTS `bank_customer_limits` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `bank_id` int(11) unsigned NOT NULL,
  `code` char(6) NOT NULL,
  `customer_type` enum('kyc','non-kyc') NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` varchar(255) NOT NULL,
  `max_balance` int(11) unsigned NOT NULL,
  `load_min` int(11) unsigned NOT NULL,
  `load_max` int(11) unsigned NOT NULL,
  `load_max_val_daily` int(11) unsigned NOT NULL,
  `load_max_val_monthly` int(11) unsigned NOT NULL,
  `load_max_val_yearly` int(11) unsigned NOT NULL,
  `txn_min` int(11) unsigned NOT NULL,
  `txn_max` int(11) unsigned NOT NULL,
  `txn_max_val_daily` int(11) unsigned NOT NULL,
  `txn_max_val_monthly` int(11) unsigned NOT NULL,
  `txn_max_val_yearly` int(11) unsigned NOT NULL,
  `date_start` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `date_created` timestamp NULL DEFAULT NULL,
  `date_updated` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `by_ops_id` int(11) unsigned NOT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 COMMENT='This is bank-purse configuration' AUTO_INCREMENT=3 ;

--
-- Dumping data for table `bank_customer_limits`
--

INSERT INTO `bank_customer_limits` (`id`, `bank_id`, `code`, `customer_type`, `name`, `description`, `max_balance`, `load_min`, `load_max`, `load_max_val_daily`, `load_max_val_monthly`, `load_max_val_yearly`, `txn_min`, `txn_max`, `txn_max_val_daily`, `txn_max_val_monthly`, `txn_max_val_yearly`, `date_start`, `date_created`, `date_updated`, `by_ops_id`, `status`) VALUES
(1, 3, 'KYC300', 'kyc', 'KYC Ratnakar', 'KYC Ratnakar', 50000, 1, 50000, 200000, 200000, 200000, 1, 10000, 50000, 200000, 2000000, '2014-08-11 02:31:44', '2014-08-11 02:31:44', '2014-09-15 05:53:51', 101, 'active'),
(2, 3, 'NKC300', 'non-kyc', 'Non-KYC Ratnakar', 'Non KYC Ratnakar', 10000, 1, 10000, 10000, 10000, 120000, 1, 10000, 10000, 10000, 120000, '2014-08-11 02:31:44', '2014-08-11 02:31:44', '2014-09-15 05:49:59', 101, 'active');

SET @flag_id := (SELECT id FROM `t_flags` WHERE name = 'operation-bank');
SET @ops_id = '3';
INSERT INTO `t_privileges` (`id` ,`name` ,`flag_id` ,`description`) VALUES (NULL , 'view', @flag_id, 'View bank details');
SET @priv_id = last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'editcustomerlimit', @flag_id, 'Edit Bank Customer Limit');
SET @priv_id = last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`,`allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'viewcustomerlimit', @flag_id, 'View Bank Customer Limit Details');
SET @priv_id = last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`,`allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');


CREATE TABLE IF NOT EXISTS `log_bank_customer_limits` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `customer_limit_id` int(11) unsigned NOT NULL,
  `bank_id` int(11) unsigned NOT NULL,
  `code` char(6) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` varchar(255) NOT NULL,
  `max_balance` int(11) unsigned NOT NULL,
  `load_min` int(11) unsigned NOT NULL,
  `load_max` int(11) unsigned NOT NULL,
  `load_max_val_daily` int(11) unsigned NOT NULL,
  `load_max_val_monthly` int(11) unsigned NOT NULL,
  `load_max_val_yearly` int(11) unsigned NOT NULL,
  `txn_min` int(11) unsigned NOT NULL,
  `txn_max` int(11) unsigned NOT NULL,
  `txn_max_val_daily` int(11) unsigned NOT NULL,
  `txn_max_val_monthly` int(11) unsigned NOT NULL,
  `txn_max_val_yearly` int(11) unsigned NOT NULL,
  `date_start` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `date_end` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `date_created` timestamp NULL DEFAULT NULL,
  `date_updated` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `by_ops_id` int(11) unsigned NOT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 COMMENT='This is bank-purse configuration';

ALTER TABLE `rat_wallet_transfer` ADD `txn_product_id` INT NOT NULL AFTER `product_id` ;