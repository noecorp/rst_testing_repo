INSERT INTO `t_products` (`id`, `bank_id`, `name`, `description`, `currency`, `ecs_product_code`, `program_type`, `unicode`, `const`, `flag_common`, `static_otp`, `by_ops_id`, `ip`, `date_created`, `status`) VALUES ('16', '3', 'RATNAKAR REMITTANCE', 'RATNAKAR REMITTANCE', 'INR', '10000042', 'Remit', '914', 'RAT_REMIT', 'yes', 'no', '101', '127000000001', '2014-07-07 16:07:36', 'active');

INSERT INTO `purse_master` (`id`, `bank_id`, `product_id`, `global_purse_id`, `code`, `name`, `description`, `max_balance`, `allow_remit`, `allow_mvc`, `load_channel`, `load_validity_day`, `load_validity_hr`, `load_validity_min`, `load_min`, `load_max`, `load_max_cnt_daily`, `load_max_val_daily`, `load_max_cnt_monthly`, `load_max_val_monthly`, `load_max_cnt_yearly`, `load_max_val_yearly`, `txn_restriction_type`, `txn_upload_list`, `txn_min`, `txn_max`, `txn_max_cnt_daily`, `txn_max_val_daily`, `txn_max_cnt_monthly`, `txn_max_val_monthly`, `txn_max_cnt_yearly`, `txn_max_val_yearly`, `priority`, `date_start`, `date_created`, `date_updated`, `by_ops_id`, `status`) VALUES (NULL, '3', '16', '1', 'REM914', 'Ratnakar Remittance Wallet', 'Ratnakar Remittance Wallet', '50000', 'no', 'no', 'api', '0', '0', '0', '750', '50000', '0', '0', '0', '200000', '0', '0', 'mcc', 'yes', '1', '10000', '0', '0', '0', '200000', '0', '0', '1', '2014-07-01 15:15:32', '2014-07-07 15:15:32', '2014-07-07 15:15:32', '101', 'active');


INSERT INTO `product_customer_limits` (`id`, `bank_id`, `product_id`, `code`, `customer_type`, `name`, `description`, `max_balance`, `load_min`, `load_max`, `load_max_val_daily`, `load_max_val_monthly`, `load_max_val_yearly`, `txn_min`, `txn_max`, `txn_max_val_daily`, `txn_max_val_monthly`, `txn_max_val_yearly`, `date_start`, `date_created`, `date_updated`, `by_ops_id`, `status`) VALUES (NULL, '3', '16', 'KYC914', 'kyc', 'KYC Ratnakar Remittance', 'KYC Ratnakar Remittance', '50000', '0', '50000', '50000', '200000', '0', '1', '50000', '50000', '200000', '0',NOW(), NOW(), NOW(), '101', 'active');

INSERT INTO `product_customer_limits` (`id`, `bank_id`, `product_id`, `code`, `customer_type`, `name`, `description`, `max_balance`, `load_min`, `load_max`, `load_max_val_daily`, `load_max_val_monthly`, `load_max_val_yearly`, `txn_min`, `txn_max`, `txn_max_val_daily`, `txn_max_val_monthly`, `txn_max_val_yearly`, `date_start`, `date_created`, `date_updated`, `by_ops_id`, `status`) VALUES (NULL, '3', '16', 'NKC914', 'non-kyc', 'Non-KYC Ratnakar Remittance', 'Non KYC Ratnakar Remittance', '50000', '0', '50000', '50000', '200000', '0', '1', '50000', '50000', '200000', '0',NOW(), NOW(), NOW(), '101', 'active');


DROP TABLE IF EXISTS `rat_beneficiaries`;
CREATE TABLE IF NOT EXISTS `rat_beneficiaries` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `remitter_id` int(11) unsigned NOT NULL,
  `name` varchar(100) NOT NULL,
  `nick_name` varchar(100) DEFAULT NULL,
  `ifsc_code` varchar(20) DEFAULT NULL,
  `bank_account_number` varchar(35) DEFAULT NULL,
  `bank_name` varchar(100) DEFAULT NULL,
  `branch_name` varchar(100) DEFAULT NULL,
  `branch_city` varchar(50) DEFAULT NULL,
  `branch_address` varchar(250) DEFAULT NULL,
  `bank_account_type` varchar(35) DEFAULT NULL,
  `address_line1` varchar(100) DEFAULT NULL,
  `address_line2` varchar(255) NOT NULL,
  `mobile` varchar(20) NOT NULL DEFAULT '0',
  `email` varchar(60) DEFAULT NULL,
  `by_agent_id` int(11) unsigned NOT NULL,
  `by_ops_id` int(11) unsigned DEFAULT NULL,
  `date_created` datetime NOT NULL,
  `date_modified` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;



DROP TABLE IF EXISTS `rat_remittance_refund`;
CREATE TABLE IF NOT EXISTS `rat_remittance_refund` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `remitter_id` int(11) NOT NULL,
  `remittance_request_id` int(11) NOT NULL,
  `agent_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `amount` decimal(11,2) NOT NULL,
  `fee` decimal(11,2) NOT NULL,
  `service_tax` decimal(11,2) NOT NULL,
  `reversal_fee` decimal(11,2) NOT NULL,
  `reversal_service_tax` decimal(11,2) NOT NULL,
  `txn_code` int(11) unsigned NOT NULL,
  `status` enum('pending','success','failure') NOT NULL DEFAULT 'pending',
  `date_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;




DROP TABLE IF EXISTS `rat_remittance_request`;
CREATE TABLE IF NOT EXISTS `rat_remittance_request` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `remitter_id` int(11) NOT NULL,
  `beneficiary_id` int(11) NOT NULL,
  `agent_id` int(11) NOT NULL,
  `ops_id` int(11) unsigned NOT NULL,
  `product_id` int(11) unsigned NOT NULL,
  `amount` decimal(11,2) NOT NULL,
  `fee` decimal(11,2) NOT NULL,
  `service_tax` decimal(11,2) NOT NULL,
  `sender_msg` varchar(180) NOT NULL,
  `txn_code` int(11) unsigned NOT NULL,
  `status` enum('in_process','success','failure','incomplete','hold','refund') NOT NULL DEFAULT 'in_process',
  `hold_reason` varchar(150) NOT NULL,
  `cr_response` varchar(150) NOT NULL,
  `final_response` varchar(150) NOT NULL,
  `fund_holder` enum('remitter','beneficiary','agent','neft','ops') NOT NULL DEFAULT 'remitter',
  `is_complete` enum('yes','no') NOT NULL DEFAULT 'no',
  `date_created` datetime NOT NULL,
  `date_updated` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;



DROP TABLE IF EXISTS `rat_remittance_status_log`;
CREATE TABLE IF NOT EXISTS `rat_remittance_status_log` (
  `remittance_request_id` int(11) NOT NULL,
  `status_old` enum('in_process','success','failure','incomplete','hold','refund') NOT NULL DEFAULT 'in_process',
  `status_new` enum('in_process','success','failure','incomplete','hold','refund') NOT NULL DEFAULT 'in_process',
  `by_remitter_id` int(11) NOT NULL,
  `by_agent_id` int(11) NOT NULL,
  `by_ops_id` int(11) unsigned NOT NULL,
  `date_created` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `rat_remit_remitters`;
CREATE TABLE IF NOT EXISTS `rat_remit_remitters` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  `middle_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
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
  `address_line2` varchar(100) NOT NULL,
  `city` varchar(50) NOT NULL,
  `state` varchar(50) NOT NULL,
  `pincode` int(10) NOT NULL,
  `mobile_country_code` int(6) NOT NULL,
  `mobile` varchar(15) NOT NULL,
  `dob` date NOT NULL,
  `mother_maiden_name` varchar(20) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `legal_id` varchar(20) DEFAULT NULL,
  `regn_fee` decimal(11,2) NOT NULL,
  `service_tax` decimal(11,2) NOT NULL,
  `txn_code` int(11) unsigned DEFAULT NULL,
  `static_code` varchar(40) NOT NULL,
  `by_agent_id` int(11) unsigned NOT NULL,
  `by_ops_id` int(11) unsigned DEFAULT NULL,
  `ip` bigint(20) NOT NULL,
  `date_created` datetime NOT NULL,
  `date_modified` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `status` enum('active','inactive','pending') NOT NULL DEFAULT 'pending',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;



DROP TABLE IF EXISTS `rat_txn_beneficiary`;
CREATE TABLE IF NOT EXISTS `rat_txn_beneficiary` (
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
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;



DROP TABLE IF EXISTS `rat_txn_remitter`;
CREATE TABLE IF NOT EXISTS `rat_txn_remitter` (
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
  `date_updated` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `txn_code` (`txn_code`,`mode`,`txn_type`) USING BTREE
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;



SET @flag_id := (SELECT id FROM `t_flags` where name ='corporate-corp_kotak_cardload');

SET @priv_id := (SELECT id FROM `t_privileges` where name ='bulkcardload' AND flag_id = @flag_id);
INSERT INTO `t_corporate_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 12, @flag_id, @priv_id, 1);
INSERT INTO `t_corporate_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 13, @flag_id, @priv_id, 1);


SET @priv_id := (SELECT id FROM `t_privileges` where name ='cardload' AND flag_id = @flag_id);
INSERT INTO `t_corporate_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 12, @flag_id, @priv_id, 1);
INSERT INTO `t_corporate_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 12, @flag_id, @priv_id, 1);


SET @flag_id := (SELECT id FROM `t_flags` where name ='corporate-corp_kotak_reports');

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'sampleload', @flag_id, 'Download sample load request file');
SET @priv_id = last_insert_id();
INSERT INTO `t_corporate_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 12, @flag_id, @priv_id, 1);
INSERT INTO `t_corporate_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 13, @flag_id, @priv_id, 1);


INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'exportsampleload', @flag_id, 'Export sample load request file');
SET @priv_id = last_insert_id();
INSERT INTO `t_corporate_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 12, @flag_id, @priv_id, 1);
INSERT INTO `t_corporate_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 13, @flag_id, @priv_id, 1);


SET @flag_id := (SELECT id FROM `t_flags` where name ='corporate-corp_kotak_cardholder');
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'downloadtxt', @flag_id, 'Download text file');
SET @priv_id = last_insert_id();
INSERT INTO `t_corporate_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 12, @flag_id, @priv_id, 1);


SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-corp_kotak_cardholder'); 
SET @ops_id = '3';

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'crnstatus', @flag_id, 'CRN Status Report');
SET @priv_id = last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');


SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-corp_ratnakar_cardholder'); 
SET @ops_id = '3';

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'crnstatus', @flag_id, 'CRN Status Report');
SET @priv_id = last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');



SET @product_id := (SELECT id FROM `t_products` where unicode ='914' AND bank_id='3' );

INSERT INTO `t_flags` (`id`, `name`, `description`, `active_on_dev`, `active_on_prod`) VALUES (NULL, 'agent-remit_ratnakar_beneficiary', 'Beneficiary section for Ratnakar Remittance', '1', '0');
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

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'failuretxn', @flag_id, 'List Failed Transactions');
SET @privilege_id = last_insert_id();
Insert into `t_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @product_id, @flag_id, @privilege_id, '1');

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'refund', @flag_id, 'Refund Failed Transactions');
SET @privilege_id = last_insert_id();
Insert into `t_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @product_id, @flag_id, @privilege_id, '1');

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'transferfundstaticcode', @flag_id, 'Fund Transfer with Static Code');
SET @privilege_id = last_insert_id();
Insert into `t_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @product_id, @flag_id, @privilege_id, '1');




INSERT INTO `t_flags` (`id`, `name`, `description`, `active_on_dev`, `active_on_prod`) VALUES (NULL, 'agent-remit_ratnakar_remitter', 'Remitter section for Ratnakar Remittance', '1', '0');
SET @flag_id = last_insert_id();

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'adddetails', @flag_id, 'Add Ratnakar remitter Details');
SET @privilege_id = last_insert_id();
Insert into `t_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @product_id, @flag_id, @privilege_id, '1');

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'registrationfee', @flag_id, 'Remitter Registration Fee Page');
SET @privilege_id = last_insert_id();
Insert into `t_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @product_id, @flag_id, @privilege_id, '1');

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'registrationcomplete', @flag_id, 'Shows the registration success message');
SET @privilege_id = last_insert_id();
Insert into `t_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @product_id, @flag_id, @privilege_id, '1');

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'transactions', @flag_id, 'See all of Transcation by Phone No.');
SET @privilege_id = last_insert_id();
Insert into `t_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @product_id, @flag_id, @privilege_id, '1');

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'transactioninfo', @flag_id, 'Transaction Detail Page');
SET @privilege_id = last_insert_id();
Insert into `t_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @product_id, @flag_id, @privilege_id, '1');



INSERT INTO `t_flags` (`id`, `name`, `description`, `active_on_dev`, `active_on_prod`) VALUES (NULL, 'agent-remit_ratnakar_reports', 'Reports for Ratnakar Remittance', '1', '0');
SET @flag_id = last_insert_id();

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'remittancereport', @flag_id, 'Remittance Report');
SET @privilege_id = last_insert_id();
Insert into `t_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @product_id, @flag_id, @privilege_id, '1');

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'exportremittancereport', @flag_id, 'Export Remittance Report');
SET @privilege_id = last_insert_id();
Insert into `t_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @product_id, @flag_id, @privilege_id, '1');

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'remittancecommission', @flag_id, 'Agent Remittance Commission Report');
SET @privilege_id = last_insert_id();
Insert into `t_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @product_id, @flag_id, @privilege_id, '1');

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'exportremittancecommission', @flag_id, 'Export Agent Remittance Commission Report');
SET @privilege_id = last_insert_id();
Insert into `t_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @product_id, @flag_id, @privilege_id, '1');


INSERT INTO `t_flags` (`id`, `name`, `description`, `active_on_dev`, `active_on_prod`) VALUES (NULL, 'operation-remit_ratnakar_remitter', 'Remitter section for Ratnakar Remittance', '1', '0');
SET @flag_id = last_insert_id();

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'search', @flag_id, 'Search Ratnakar remitter Details');
SET @priv_id = last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, '3', @flag_id, @priv_id, '1');

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'holdtransactions', @flag_id, 'Process Hold Transactions');
SET @priv_id = last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, '3', @flag_id, @priv_id, '1');

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'checkstatus', @flag_id, 'Process Hold Transactions');
SET @priv_id = last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, '3', @flag_id, @priv_id, '1');

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'beneficiary', @flag_id, 'Beneficiary details');
SET @priv_id = last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, '3', @flag_id, @priv_id, '1');

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'index', @flag_id, 'Index page of Ratnakar Remittance module in Ops');
SET @priv_id = last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, '3', @flag_id, @priv_id, '1');
