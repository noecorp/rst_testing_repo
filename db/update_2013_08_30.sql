-- ----------------------------
-- Table structure for `kotak_remit_remitters`
-- ----------------------------
DROP TABLE IF EXISTS `kotak_remit_remitters`;
CREATE TABLE `kotak_remit_remitters` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `profile_photo` varchar(100) NOT NULL,
  `product_id` int(11) NOT NULL,
  `unicode` bigint(20) unsigned NOT NULL,
  `ifsc_code` varchar(20) DEFAULT NULL,
  `bank_account_number` varchar(35) DEFAULT NULL,
  `bank_name` varchar(100) DEFAULT NULL,
  `branch_name` varchar(100) DEFAULT NULL,
  `branch_city` varchar(50) DEFAULT NULL,
  `branch_address` varchar(250) DEFAULT NULL,
  `bank_account_type` varchar(35) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `mobile_country_code` int(6) NOT NULL,
  `mobile` varchar(15) NOT NULL,
  `dob` date NOT NULL,
  `mother_maiden_name` varchar(20) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `regn_fee` decimal(11,2) NOT NULL,
  `service_tax` decimal(11,2) NOT NULL,
  `txn_code` int(11) unsigned DEFAULT NULL,
  `by_agent_id` int(11) unsigned NOT NULL,
  `by_ops_id` int(11) unsigned DEFAULT NULL,
  `ip` bigint(20) NOT NULL,
  `date_created` datetime NOT NULL,
  `date_modified` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `status` enum('active','inactive','pending') NOT NULL DEFAULT 'pending',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Table structure for `kotak_remittance_request`
-- ----------------------------
DROP TABLE IF EXISTS `kotak_remittance_request`;
CREATE TABLE `kotak_remittance_request` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `remitter_id` int(11) NOT NULL,
  `beneficiary_id` int(11) NOT NULL,
  `agent_id` int(11) NOT NULL,
  `product_id` int(11) unsigned NOT NULL,
  `amount` decimal(11,2) NOT NULL,
  `fee` decimal(11,2) NOT NULL,
  `service_tax` decimal(11,2) NOT NULL,
  `sender_msg` varchar(180) NOT NULL,
  `txn_code` int(11) unsigned NOT NULL,
  `status` enum('in_process','success','failure','incomplete') NOT NULL DEFAULT 'in_process',
  `fund_holder` enum('remitter','beneficiary','agent','neft') NOT NULL DEFAULT 'remitter',
  `is_complete` enum('yes','no') NOT NULL DEFAULT 'no',
  `date_created` datetime NOT NULL,
  `date_updated` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Table structure for `kotak_remittance_status_log`
-- ----------------------------
DROP TABLE IF EXISTS `kotak_remittance_status_log`;
CREATE TABLE `kotak_remittance_status_log` (
  `remittance_request_id` int(11) NOT NULL,
  `status_old` enum('in_process','success','failure','incomplete') NOT NULL DEFAULT 'in_process',
  `status_new` enum('in_process','success','failure','incomplete') NOT NULL DEFAULT 'in_process',
  `by_remitter_id` int(11) NOT NULL,
  `by_agent_id` int(11) NOT NULL,
  `date_created` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Table structure for `kotak_txn_remitter`
-- ----------------------------
DROP TABLE IF EXISTS `kotak_txn_remitter`;
CREATE TABLE `kotak_txn_remitter` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `txn_code` int(11) unsigned NOT NULL,
  `remitter_id` int(11) unsigned NOT NULL,
  `txn_agent_id` int(11) unsigned DEFAULT NULL,
  `txn_ops_id` int(11) unsigned DEFAULT NULL,
  `product_id` int(11) unsigned DEFAULT NULL,
  `remittance_request_id` int(11) unsigned DEFAULT NULL,
  `ip` varchar(15) NOT NULL,
  `currency` char(3) NOT NULL,
  `amount` decimal(11,2) NOT NULL DEFAULT '0.00',
  `mode` enum('cr','dr') NOT NULL,
  `txn_type` char(4) NOT NULL,
  `txn_status` enum('pending','success','failure') NOT NULL DEFAULT 'pending',
  `remarks` varchar(255) NOT NULL,
  `date_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `txn_code` (`txn_code`,`mode`,`txn_type`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Table structure for `kotak_txn_beneficiary`
-- ----------------------------
DROP TABLE IF EXISTS `kotak_txn_beneficiary`;
CREATE TABLE `kotak_txn_beneficiary` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `txn_code` int(11) unsigned NOT NULL,
  `beneficiary_id` int(11) unsigned NOT NULL,
  `txn_agent_id` int(11) unsigned DEFAULT NULL,
  `txn_ops_id` int(11) unsigned DEFAULT NULL,
  `txn_remitter_id` int(11) unsigned DEFAULT NULL,
  `product_id` int(11) unsigned DEFAULT NULL,
  `remittance_request_id` int(11) unsigned DEFAULT NULL,
  `ip` varchar(15) NOT NULL,
  `currency` char(3) NOT NULL,
  `amount` decimal(11,2) NOT NULL DEFAULT '0.00',
  `mode` enum('cr','dr') NOT NULL,
  `txn_type` char(4) NOT NULL,
  `txn_status` enum('pending','success','failure') NOT NULL DEFAULT 'pending',
  `remarks` varchar(255) NOT NULL,
  `date_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `txn_code` (`txn_code`,`mode`,`txn_type`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;


ALTER TABLE `t_txn_agent`
ADD COLUMN `kotak_remitter_id`  int(11) UNSIGNED NULL AFTER `txn_remitter_id`,
ADD COLUMN `kotak_remittance_request_id`  int(11) UNSIGNED NULL AFTER `remittance_request_id`;
 

INSERT INTO `t_unicode_conf` VALUES ('40041000', '400', '410');

INSERT INTO `t_bank` VALUES ('4', 'KOTAK MAHINDRA BANK', 'KKBK0000958', 'MUMBAI', 'MUMBAI-NARIMAN POINT', '5 C/ II, Mittal Court 224, Nariman Point, Mumbai - 400 021', '400', null, '101', '127000000001', '2013-08-30 13:02:04', 'active');
INSERT INTO `t_log_bank` VALUES ('4', 'KOTAK MAHINDRA BANK', 'KKBK0000958', 'MUMBAI', 'MUMBAI-NARIMAN POINT', '5 C/ II, Mittal Court 224, Nariman Point, Mumbai - 400 021', '400', '101', '127000000001', '2013-08-30 13:02:04', 'active');

INSERT INTO `t_products` VALUES (NULL, '4', 'Kotak Bank Shmart Transfer', 'Immediate Payment Service by Kotak Bank', 'INR', '10000026', 'Remit', '410', '101', '127000000001', '2013-08-30 13:03:09', 'active');

SET @product_id := (select id from t_products where name='Kotak Bank Shmart Transfer' LIMIT 1);
INSERT INTO `t_log_products` VALUES (product_id, '4', 'Kotak Bank Shmart Transfer', 'Immediate Payment Service by Kotak Bank', 'INR', '10000026', 'Remit', '410', '101', '127000000001', '2013-08-30 13:03:10', 'active');

INSERT INTO `t_flags` (`id`, `name`, `description`, `active_on_dev`, `active_on_prod`) VALUES (NULL, 'agent-remit_kotak_remitter', 'Remitter section for Kotak Remittance', '1', '0');
SET @flag_id = last_insert_id();

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'adddetails', @flag_id, 'Add Kotak remitter Details');
SET @privilege_id = last_insert_id();
Insert into `t_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @product_id, @flag_id, @privilege_id, '1');


INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'registrationfee', @flag_id, 'Remitter Registration Fee Page');
SET @privilege_id = last_insert_id();
Insert into `t_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @product_id, @flag_id, @privilege_id, '1');

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'registrationcomplete', @flag_id, 'Shows the registration success message');
SET @privilege_id = last_insert_id();
Insert into `t_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @product_id, @flag_id, @privilege_id, '1');


INSERT INTO `t_flags` (`id`, `name`, `description`, `active_on_dev`, `active_on_prod`) VALUES (NULL, 'agent-remit_kotak_beneficiary', 'Beneficiary section for Kotak Remittance', '1', '0');
SET @flag_id = last_insert_id();

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'searchremitter', @flag_id, 'Search remitter on the basis of registered mobile');
SET @privilege_id = last_insert_id();
Insert into `t_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @product_id, @flag_id, @privilege_id, '1');


INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'add', @flag_id, 'Add beneficiary');
SET @privilege_id = last_insert_id();
Insert into `t_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @product_id, @flag_id, @privilege_id, '1');

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'complete', @flag_id, 'Add Beneficiary complete page');
SET @privilege_id = last_insert_id();
Insert into `t_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @product_id, @flag_id, @privilege_id, '1');

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'transferfund', @flag_id, 'Transfer fund in beneficiary account');
SET @privilege_id = last_insert_id();
Insert into `t_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @product_id, @flag_id, @privilege_id, '1');

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'addbeneficiary', @flag_id, 'Add beneficiary');
SET @privilege_id = last_insert_id();
Insert into `t_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @product_id, @flag_id, @privilege_id, '1');

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'deactivatebeneficiary', @flag_id, 'Deactivate beneficiary');
SET @privilege_id = last_insert_id();
Insert into `t_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @product_id, @flag_id, @privilege_id, '1');
