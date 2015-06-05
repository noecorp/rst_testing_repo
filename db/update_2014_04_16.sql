DROP TABLE IF EXISTS `log_product_customer_limits`;
CREATE TABLE `log_product_customer_limits` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `customer_limit_id` int(11) unsigned NOT NULL,
  `bank_id` int(11) unsigned NOT NULL,
  `product_id` int(11) unsigned NOT NULL,
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
  `date_start` date NOT NULL,
  `date_end` date NOT NULL,
  `date_created` timestamp NULL DEFAULT NULL,
  `date_updated` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `by_ops_id` int(11) unsigned NOT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='This is product-purse configuration';


DROP TABLE IF EXISTS `product_customer_limits`;
CREATE TABLE `product_customer_limits` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `bank_id` int(11) unsigned NOT NULL,
  `product_id` int(11) unsigned NOT NULL,
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
  `date_start` date NOT NULL,
  `date_created` timestamp NULL DEFAULT NULL,
  `date_updated` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `by_ops_id` int(11) unsigned NOT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=latin1 COMMENT='This is product-purse configuration';


INSERT INTO `product_customer_limits` VALUES ('1', '3', '3', 'KYC310', 'KYC Medi-Assist', 'KYC Medi-Assist', '50000', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '2014-02-22', '2013-08-01 18:34:18', '2014-04-16 11:39:30', '102', 'active');
INSERT INTO `product_customer_limits` VALUES ('2', '3', '3', 'NKC310', 'Non-KYC Medi-Assist', 'Non-KYC Medi-Assist', '50000', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '2014-01-20', '2013-08-01 18:34:18', '2014-04-16 11:39:40', '102', 'active');
INSERT INTO `product_customer_limits` VALUES ('3', '4', '6', 'KYC510', 'KYC Kotak Amul', 'KYC Kotak Amul', '50000', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '2014-01-23', '2013-11-19 22:34:18', '2014-04-16 11:39:23', '102', 'active');
INSERT INTO `product_customer_limits` VALUES ('4', '4', '6', 'NKC510', 'Non-KYC Kotak Amul', 'Non-KYC Kotak Amul', '50000', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '2014-04-04', '2014-01-16 08:18:18', '2014-04-16 11:40:28', '102', 'active');
INSERT INTO `product_customer_limits` VALUES ('5', '2', '7', 'KYC710', 'KYC BOI NSDC', 'KYC BOI NSDC', '50000', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '2014-03-20', '2014-03-12 04:37:54', '2014-04-16 11:46:06', '102', 'active');
INSERT INTO `product_customer_limits` VALUES ('6', '2', '7', 'NKC710', 'Non-KYC BOI NSDC', 'Non-KYC BOI NSDC', '50000', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '2014-03-20', '2014-03-12 08:08:18', '2014-04-16 11:46:15', '102', 'active');
INSERT INTO `product_customer_limits` VALUES ('7', '3', '8', 'KYC810', 'KYC Paytronics', 'KYC Paytronics', '50000', '0', '0', '0', '200000', '200000', '0', '0', '50000', '200000', '2000000', '0000-00-00', null, '2014-04-16 11:43:09', '0', 'active');
INSERT INTO `product_customer_limits` VALUES ('8', '3', '8', 'NKC810', 'Non-KYC Paytronics', 'Non-KYC Paytronics', '10000', '0', '0', '0', '10000', '100000', '0', '0', '10000', '10000', '100000', '0000-00-00', null, '2014-04-16 11:43:31', '0', 'active');


-- Added Product Const

UPDATE `t_products` SET `const`='AXIS_MVC' WHERE (`id`='1');

SET @flag_id := (select id from t_flags where name='operation-product' LIMIT 1);
SET @ops_id = '3';

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'editcustomerlimit', @flag_id, 'Edit Product Customer Limit');
SET @priv_id = last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`,`allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'viewcustomerlimit', @flag_id, 'View Product Customer Limit Details');
SET @priv_id = last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`,`allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');


UPDATE purse_master SET code = 'RPG810', name = 'Ratnakar Paytronic Wallet', description = 'Ratnakar Paytronic Wallet', max_balance = 50000, load_min = 750, load_max = 50000, load_max_cnt_daily = 0, 
load_max_val_daily = 0, load_max_cnt_monthly = 0, load_max_val_monthly = 200000, load_max_cnt_yearly = 0, load_max_val_yearly = 0, txn_max_cnt_daily = 0,
txn_max_val_daily = 0, txn_max_cnt_monthly = 0, txn_max_val_monthly = 200000, txn_max_cnt_yearly = 0, txn_max_val_yearly = 0 where code = 'RPN810';

DELETE from purse_master WHERE code = 'RPK810' LIMIT 1;

