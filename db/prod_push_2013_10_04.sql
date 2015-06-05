SET @flag_id := (SELECT id FROM `t_flags` where name ='agent-remit_boi_beneficiary');
INSERT INTO `t_privileges` (`name`, `flag_id`, `description`) VALUES ('deactivatebeneficiary',@flag_id , 'Deactivate Beneficiary');
SET @product_id := (SELECT id from t_products WHERE program_type = 'Remit'); 
SET @priv_id := (SELECT id FROM t_privileges WHERE name = 'deactivatebeneficiary');
INSERT INTO `t_product_privileges` (`product_id`, `flag_id`, `privilege_id`, `allow`) VALUES
(@product_id, @flag_id, @priv_id, 1);

SET @flag_id := (SELECT id FROM `t_flags` WHERE name='operation-agents');
INSERT INTO `t_privileges` (`name`, `flag_id`, `description`) VALUES ('addauthemail', @flag_id, 'Add Auth email');

INSERT INTO `t_unicode_conf` (`unicode_initials`, `bank_unicode`, `product_unicode`) VALUES ('30031000', '300', '310');

ALTER TABLE `t_remitters` ADD `profile_photo` VARCHAR( 100 ) NOT NULL AFTER `name`;

ALTER TABLE `t_agent_details` ADD `auth_email` VARCHAR( 100 ) NOT NULL AFTER `email` ;


DROP TABLE IF EXISTS `log_master`;
CREATE TABLE `log_master` (
  `date_stamped` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `by_id` int(11) unsigned DEFAULT NULL,
  `by_whom` enum('customer','bank','corporate','agent','helpdesk','ops') DEFAULT NULL,
  `functionality` varchar(100) DEFAULT NULL,
  `session_id` varchar(100) DEFAULT NULL,
  `table_name` varchar(100) DEFAULT NULL,
  `txt_old` text,
  `txt_new` text,
  `remarks` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


----------------------------------------------------------------------------------

ALTER TABLE `t_email_verification` ADD `agent_detail_id` INT( 10 ) UNSIGNED NOT NULL AFTER `agent_id`;
ALTER TABLE `t_agent_details` ADD `auth_email_verification_id` INT( 11 ) UNSIGNED NOT NULL AFTER `by_ops_id` ,
ADD `auth_email_verification_status` ENUM( 'pending', 'verified' ) NOT NULL DEFAULT 'pending' AFTER `auth_email_verification_id`;
INSERT INTO `t_flags` (`id`, `name`, `description`, `active_on_dev`, `active_on_prod`) VALUES (NULL, 'agent-authemailauthorization', 'Agent Auth Email authorization', '1', '0');
SET @flag_id := last_insert_id();
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'index', @flag_id, 'Agent Auth Email authorization');

SET @flag_id := (SELECT id FROM t_flags WHERE name = 'operation-approvedagent');
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'resendauthemailverificationemail', @flag_id, 'Resend Auth Email');

ALTER TABLE  `t_beneficiaries` CHANGE  `mobile`  `mobile` VARCHAR( 20 )  NOT NULL DEFAULT  '0',
CHANGE  `email`  `email` VARCHAR( 60 ) NULL DEFAULT NULL, CHANGE  `branch_address`  `branch_address` VARCHAR( 350 )  NULL DEFAULT NULL;

SET @flag_id := (SELECT id FROM t_flags WHERE name = 'operation-history');
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'cron', @flag_id, 'See Cron Logs');


ALTER TABLE  `t_agent_closing_balance` ADD  `date_updated` TIMESTAMP NOT NULL;



ALTER TABLE `t_txn_agent`
ADD COLUMN `txn_customer_master_id`  int(11) UNSIGNED NOT NULL AFTER `agent_id`,
ADD COLUMN `purse_master_id`  int(11) UNSIGNED NOT NULL AFTER `remittance_request_id`,
ADD COLUMN `customer_purse_id`  int(11) UNSIGNED NOT NULL AFTER `purse_master_id`;


INSERT INTO `t_cron` (`id`, `name`, `description`, `file_name`, `status`, `status_cron`, `date_updated`) VALUES ('15', 'Ratnakar Corp ECS cardholder registration', 'Ratnakar Corp ECS cardholder registration', 'RatCorpECSRegn.php', 'active', 'completed', CURRENT_TIMESTAMP);


INSERT INTO `t_cron` (`id`, `name`, `description`, `file_name`, `status`, `status_cron`, `date_updated`) VALUES (16, 'Load Medi Assist Customer', 'Cron will load medi assist customer with ECS', 'LoadMediAssistCustomer.php', 'active', 'completed', '2013-08-08 10:56:14');

-----------------


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
 
-----------------------------

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


----------------------------

 CREATE  TABLE  `kotak_beneficiaries` (  `id` int( 11  )  unsigned NOT  NULL  AUTO_INCREMENT ,
 `remitter_id` int( 11  )  unsigned NOT  NULL ,
 `name` varchar( 100  )  NOT  NULL ,
 `nick_name` varchar( 100  )  DEFAULT NULL ,
 `ifsc_code` varchar( 20  )  DEFAULT NULL ,
 `bank_account_number` varchar( 35  )  DEFAULT NULL ,
 `bank_name` varchar( 100  )  DEFAULT NULL ,
 `branch_name` varchar( 100  )  DEFAULT NULL ,
 `branch_city` varchar( 50  )  DEFAULT NULL ,
 `branch_address` varchar( 250  )  DEFAULT NULL ,
 `bank_account_type` varchar( 35  )  DEFAULT NULL ,
 `address_line1` varchar( 100  )  DEFAULT NULL ,
 `address_line2` varchar( 255  )  NOT  NULL ,
 `mobile` varchar( 15  )  NOT  NULL DEFAULT  '0',
 `email` varchar( 50  )  DEFAULT NULL ,
 `by_agent_id` int( 11  )  unsigned NOT  NULL ,
 `by_ops_id` int( 11  ) unsigned  DEFAULT NULL ,
 `date_created` datetime NOT  NULL ,
 `date_modified` timestamp NOT  NULL DEFAULT  '0000-00-00 00:00:00' ON  UPDATE  CURRENT_TIMESTAMP ,
 `status` enum(  'active',  'inactive'  )  NOT  NULL DEFAULT  'active',
 PRIMARY  KEY (  `id`  )  ) ENGINE  = InnoDB  DEFAULT CHARSET  = latin1;

ALTER TABLE `t_change_status_log` ADD `kotak_beneficiary_id` INT( 11 ) UNSIGNED NOT NULL AFTER `beneficiary_id`;

-------------------------------------------------------------------------------

ALTER TABLE `t_txn_ops`
ADD COLUMN `kotak_remitter_id`  int(11) UNSIGNED NULL AFTER `txn_remitter_id`,
ADD COLUMN `kotak_remittance_request_id`  int(11) UNSIGNED NULL AFTER `remittance_request_id`;

---------------------------------------------------------------------------------


ALTER TABLE `kotak_remittance_request` CHANGE `status` `status` ENUM( 'in_process', 'success', 'failure', 'incomplete', 'hold', 'refund', 'fail_on_hold' ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT 'in_process';
ALTER TABLE `kotak_remittance_request` ADD `hold_reason` TEXT NOT NULL AFTER `status`;

ALTER TABLE `kotak_txn_remitter`
ADD COLUMN `date_updated`  timestamp NULL DEFAULT NULL AFTER `date_created`;

ALTER TABLE `t_txn_ops`
ADD COLUMN `date_updated`  timestamp NULL DEFAULT NULL AFTER `date_created`;

ALTER TABLE `t_txn_ops`
ADD COLUMN `kotak_beneficiary_id`  int(11) UNSIGNED NULL AFTER `txn_beneficiary_id`;


ALTER TABLE `kotak_beneficiaries`
MODIFY COLUMN `mobile`  varchar(20) NOT NULL DEFAULT '0' AFTER `address_line2`;

ALTER TABLE `kotak_beneficiaries`
MODIFY COLUMN `email`  varchar(60) NULL DEFAULT NULL AFTER `mobile`;


UPDATE t_bank SET logo = 'logo-kotak.jpg' WHERE name = 'KOTAK MAHINDRA BANK';


INSERT INTO `t_flags` (`id`, `name`, `description`, `active_on_dev`, `active_on_prod`) VALUES (NULL, 'operation-remit_kotak_remitter', 'Remitter section for Kotak Remittance', '1', '0');
SET @flag_id = last_insert_id();

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'search', @flag_id, 'Search Kotak remitter Details');


ALTER TABLE `kotak_remittance_request` ADD `cr_response` TEXT NOT NULL AFTER `hold_reason`;
ALTER TABLE `kotak_remittance_request` ADD `final_response` TEXT NOT NULL AFTER `cr_response`;

ALTER TABLE `kotak_remittance_request` CHANGE `hold_reason` `hold_reason` VARCHAR( 150 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL ,
CHANGE `cr_response` `cr_response` VARCHAR( 150 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL ,
CHANGE `final_response` `final_response` VARCHAR( 150 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL;



SET @flag_id := (SELECT id FROM t_flags WHERE name = 'operation-helpdesk');
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'kotakremittance', @flag_id, 'See all of Transcation by Phone No.');
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'txninfo', @flag_id, 'See Transcation by Txn Code');



SET @flag_id := (SELECT id FROM `t_flags` WHERE name='operation-remit_kotak_remitter' LIMIT 1);
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'holdtransactions', @flag_id, 'Process Hold Transactions');
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'processtransaction', @flag_id, 'Process Hold Transactions');
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'checkstatus', @flag_id, 'Process Hold Transactions');



ALTER TABLE `kotak_remittance_request` CHANGE `status` `status` ENUM( 'in_process', 'success', 'failure', 'incomplete', 'hold', 'refund' )  NOT NULL DEFAULT 'in_process';

CREATE TABLE `kotak_remittance_refund` (
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
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

ALTER TABLE `kotak_remittance_request` CHANGE `fund_holder` `fund_holder` ENUM( 'remitter', 'beneficiary', 'agent', 'neft', 'ops' ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT 'remitter';

---------------------
SET @product_id := (select id from t_products where name='Kotak Bank Shmart Transfer' LIMIT 1);
SET @flag_id := (select id from t_flags where name='agent-remit_kotak_beneficiary' LIMIT 1);


INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'failuretxn', @flag_id, 'List Failed Transactions');
SET @privilege_id = last_insert_id();
Insert into `t_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @product_id, @flag_id, @privilege_id, '1');


INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'refund', @flag_id, 'Refund Failed Transactions');
SET @privilege_id = last_insert_id();
Insert into `t_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @product_id, @flag_id, @privilege_id, '1');

------------

ALTER TABLE `kotak_remittance_request` ADD `ops_id` INT( 11 ) UNSIGNED NOT NULL AFTER `agent_id`; 


ALTER TABLE `kotak_remittance_status_log` CHANGE `status_old` `status_old` ENUM( 'in_process', 'success', 'failure', 'incomplete', 'hold', 'refund' ) NOT NULL DEFAULT 'in_process',
CHANGE `status_new` `status_new` ENUM( 'in_process', 'success', 'failure', 'incomplete', 'hold', 'refund' ) NOT NULL DEFAULT 'in_process';

---------------

SET @product_id := (select id from t_products where name='Kotak Bank Shmart Transfer' LIMIT 1);
SET @flag_id := (select id from t_flags where name='agent-Remit_Kotak_Remitter' LIMIT 1);


INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'transactions', @flag_id, 'See all of Transcation by Phone No.');
SET @privilege_id = last_insert_id();
Insert into `t_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @product_id, @flag_id, @privilege_id, '1');

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'transactioninfo', @flag_id, 'Transaction Detail Page');
SET @privilege_id = last_insert_id();
Insert into `t_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @product_id, @flag_id, @privilege_id, '1');

-------------

SET @flag_id := (SELECT id FROM `t_flags` WHERE name='operation-remit_kotak_remitter' LIMIT 1);
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'beneficiary', @flag_id, 'Beneficiary details');

INSERT INTO `t_flags` (`id`, `name`,`description`) VALUES (NULL,'operation-agentfunding','Agent Funding');
SET @flag_id = LAST_INSERT_ID();
Insert into `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'uploadbankstatement', @flag_id, 'Upload Bank Statement');


ALTER TABLE `kotak_remit_remitters` ADD `legal_id` VARCHAR( 20 ) NULL AFTER `email`;

 CREATE TABLE `bank_statement` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `bank_stt_name` varchar(50) DEFAULT NULL,
  `txn_date` varchar(20) DEFAULT NULL,
  `description` varchar(100) DEFAULT NULL,
  `journal_no` varchar(50) DEFAULT NULL,
  `mode` enum('cr','dr') DEFAULT NULL,
  `amount` decimal(11,2) DEFAULT NULL,
  `balance` decimal(11,2) DEFAULT NULL,
  `is_duplicate` enum('yes','no') NOT NULL DEFAULT 'no',
  `is_settled` enum('yes','no') NOT NULL DEFAULT 'no',
  `by_ops_id` int(11) unsigned NOT NULL,
  `ip` varchar(15) NOT NULL,
  `date_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



ALTER TABLE `t_txn_ops`
ADD COLUMN `agent_funding_id`  int(11) UNSIGNED NULL AFTER `agent_fund_request_id`;

ALTER TABLE `t_txn_agent`
ADD COLUMN `agent_funding_id`  int(11) UNSIGNED NULL AFTER `agent_fund_request_id`;



ALTER TABLE `bank_statement`
ADD COLUMN `cheque_no`  varchar(50) NULL AFTER `journal_no`;

----------------------------------------------------------


DROP TABLE IF EXISTS `bank_statement`;
CREATE TABLE `bank_statement` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `bank_stt_name` varchar(50) DEFAULT NULL,
  `txn_date` varchar(20) DEFAULT NULL,
  `description` varchar(100) DEFAULT NULL,
  `journal_no` varchar(50) DEFAULT NULL,
  `cheque_no` varchar(50) DEFAULT NULL,
  `mode` enum('cr','dr') DEFAULT NULL,
  `amount` decimal(11,2) DEFAULT NULL,
  `balance` decimal(11,2) DEFAULT NULL,
  `status` enum('new','duplicate','unsettled','settled') NOT NULL DEFAULT 'new',
  `by_ops_id` int(11) unsigned NOT NULL,
  `ip` varchar(15) NOT NULL,
  `date_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of bank_statement
-- ----------------------------

DROP TABLE IF EXISTS `agent_funding`;
CREATE TABLE `agent_funding` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `agent_id` int(11) unsigned NOT NULL,
  `amount` decimal(11,2) NOT NULL DEFAULT '0.00',
  `fund_transfer_type_id` int(11) unsigned NOT NULL,
  `journal_no` varchar(50) DEFAULT NULL,
  `cheque_no` varchar(50) DEFAULT NULL,
  `cheque_details` varchar(255) DEFAULT NULL,
  `comments` varchar(255) NOT NULL,
  `approved_by` enum('system','ops') DEFAULT NULL,
  `by_ops_id` int(11) unsigned DEFAULT NULL,
  `ip_agent` varchar(15) NOT NULL,
  `ip_ops` varchar(15) DEFAULT NULL,
  `date_request` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_settlement` timestamp NULL DEFAULT NULL,
  `settlement_remarks` varchar(255) DEFAULT NULL,
  `status` enum('approve','pending','decline') NOT NULL DEFAULT 'pending',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;


INSERT INTO `t_flags` (`id`, `name`,`description`) VALUES (NULL,'agent-agentfunding','Agent Funding');
SET @flag_id = LAST_INSERT_ID();
Insert into `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'index', @flag_id, 'Agent Funding');

SET @privilege_id = last_insert_id();
SET @product_id := (SELECT id FROM t_products WHERE unicode = '110' AND status = 'active' LIMIT 1);
Insert into `t_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @product_id, @flag_id, @privilege_id, '1');
SET @product_id := (SELECT id FROM t_products WHERE unicode = '210' AND status = 'active' LIMIT 1);
Insert into `t_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @product_id, @flag_id, @privilege_id, '1');
SET @product_id := (SELECT id FROM t_products WHERE unicode = '310' AND status = 'active' LIMIT 1);
Insert into `t_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @product_id, @flag_id, @privilege_id, '1');
SET @product_id := (SELECT id FROM t_products WHERE unicode = '410' AND status = 'active' LIMIT 1);
Insert into `t_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @product_id, @flag_id, @privilege_id, '1');



INSERT INTO `t_cron` (`id` ,`name` ,`description` ,`file_name` ,`status` ,`status_cron` ,`date_updated`)
VALUES (
18 , 'Agent Funding To Check Duplicate Bank Statements ', 'Agent funding to check duplicate bank statements with condition with condations journal no./cheque no. and amount and status (''duplicate'' or ''unsettled'' or ''settled''). If record exist then mark its status duplicate else mark its status unsettled.', 'AgentFundingCheckDuplicate', 'active', 'completed',
CURRENT_TIMESTAMP
);



ALTER TABLE `kotak_remittance_status_log`
ADD COLUMN `by_ops_id`  int(11) UNSIGNED NOT NULL AFTER `by_agent_id`;

ALTER TABLE `bank_statement`
ADD COLUMN `date_updated`  timestamp NULL AFTER `date_created`;


ALTER TABLE `agent_funding`
MODIFY COLUMN `status` enum('approve','pending','decline','duplicate') CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT 'pending' AFTER `settlement_remarks`;


ALTER TABLE `agent_funding`
MODIFY COLUMN `status` enum('approve','pending','decline','duplicate')  NOT NULL DEFAULT 'pending' AFTER `settlement_remarks`,
ADD COLUMN `bank_statement_id`  int(11) UNSIGNED NOT NULL AFTER `comments`;


ALTER TABLE `agent_funding` CHANGE `status` `status` ENUM( 'approved', 'pending', 'rejected', 'duplicate' ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT 'pending';




SET @flag_id := (SELECT id FROM `t_flags` WHERE name='operation-agentsummary' LIMIT 1);

SET @priv_id := (SELECT id FROM `t_privileges` WHERE name='loadfund' AND flag_id = @flag_id LIMIT 1);
DELETE FROM t_flippers WHERE flag_id = @flag_id AND privilege_id = @priv_id ;
DELETE FROM t_privileges WHERE id = @priv_id LIMIT 1;

SET @priv_id := (SELECT id FROM `t_privileges` WHERE name='confirm' AND flag_id = @flag_id LIMIT 1);
DELETE FROM t_flippers WHERE flag_id = @flag_id AND privilege_id = @priv_id ;
DELETE FROM t_privileges WHERE id = @priv_id LIMIT 1;


SET @product_id := (select id from t_products where name='Kotak Bank Shmart Transfer' LIMIT 1);

INSERT INTO `t_flags` (`id`, `name`, `description`, `active_on_dev`, `active_on_prod`) VALUES (NULL, 'agent-remit_kotak_reports', 'Reports for Kotak Remittance', '1', '0');
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

SET @priv_id := (select id from `t_privileges` where name='feereport' LIMIT 1);
SET @flg_id := (select flag_id from `t_privileges` where name='feereport' AND id = @priv_id LIMIT 1);
Insert into `t_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @product_id, @flg_id, @priv_id, '1');


SET @priv_id := (select id from `t_privileges` where name='agentsummary' LIMIT 1);
SET @flg_id := (select flag_id from `t_privileges` where name='agentsummary' AND id = @priv_id LIMIT 1);
Insert into `t_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @product_id, @flg_id, @priv_id, '1');

SET @priv_id := (select id from `t_privileges` where name='exportagentsummary' LIMIT 1);
SET @flg_id := (select flag_id from `t_privileges` where name='exportagentsummary' AND id = @priv_id LIMIT 1);
Insert into `t_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @product_id, @flg_id, @priv_id, '1');



SET @priv_id := (select id from `t_privileges` where name='agentcommissionsummary' LIMIT 1);
SET @flg_id := (select flag_id from `t_privileges` where name='agentcommissionsummary' AND id = @priv_id LIMIT 1);
Insert into `t_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @product_id, @flg_id, @priv_id, '1');

SET @priv_id := (select id from `t_privileges` where name='exportagentcommissionsummary' LIMIT 1);
SET @flg_id := (select flag_id from `t_privileges` where name='exportagentcommissionsummary' AND id = @priv_id LIMIT 1);
Insert into `t_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @product_id, @flg_id, @priv_id, '1');

----------------------------------------------------------


SET @flag_id := (SELECT id FROM `t_flags` WHERE name='agent-agentfunding');

INSERT INTO `t_privileges` (`id` ,`name` ,`flag_id` ,`description`)
VALUES (NULL , 'viewfundrequest', @flag_id, 'Agent should be able to view his fund request details.');

SET @privilege_id = last_insert_id();
SET @product_id := (SELECT id FROM t_products WHERE unicode = '110' AND status = 'active' LIMIT 1);
Insert into `t_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @product_id, @flag_id, @privilege_id, '1');
SET @product_id := (SELECT id FROM t_products WHERE unicode = '210' AND status = 'active' LIMIT 1);
Insert into `t_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @product_id, @flag_id, @privilege_id, '1');
SET @product_id := (SELECT id FROM t_products WHERE unicode = '410' AND status = 'active' LIMIT 1);
Insert into `t_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @product_id, @flag_id, @privilege_id, '1');


SET @flag_id = (SELECT id FROM `t_flags` WHERE name='operation-agentfunding'); 
Insert into `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'index', @flag_id, 'Operation Agent Funding Index');

----------------------------------------------------------


ALTER TABLE `agent_funding`
DROP COLUMN `journal_no`,
DROP COLUMN `cheque_no`,
CHANGE COLUMN `cheque_details` `funding_details`  varchar(255) NULL DEFAULT NULL ,
MODIFY COLUMN `ip_agent`  varchar(15) NOT NULL AFTER `comments`,
MODIFY COLUMN `date_request`  timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `ip_agent`,
MODIFY COLUMN `bank_statement_id`  int(11) UNSIGNED NOT NULL AFTER `date_request`,
CHANGE COLUMN `approved_by` `settlement_by`  enum('system','ops') NULL AFTER `bank_statement_id`,
CHANGE COLUMN `by_ops_id` `settlement_by_ops_id`  int(11) UNSIGNED NULL DEFAULT NULL AFTER `settlement_by`,
CHANGE COLUMN `ip_ops` `settlement_ip_ops`  varchar(15)  NULL DEFAULT NULL AFTER `settlement_by_ops_id`,
CHANGE COLUMN `date_settlement` `settlement_date`  timestamp NULL DEFAULT NULL AFTER `settlement_ip_ops`,
ADD COLUMN `funding_no`  varchar(50) NOT NULL AFTER `fund_transfer_type_id`;


ALTER TABLE `bank_statement`
DROP COLUMN `journal_no`,
DROP COLUMN `cheque_no`,
ADD COLUMN `fund_transfer_type_id`  int(11) UNSIGNED NOT NULL AFTER `description`,
ADD COLUMN `funding_no`  varchar(50) NOT NULL AFTER `fund_transfer_type_id`;

-------------

UPDATE t_beneficiaries SET bank_account_number=AES_ENCRYPT(bank_account_number, 'goprs010058074ea3dc0bc89ge8aprcf'), branch_address=AES_ENCRYPT(branch_address, 'goprs010058074ea3dc0bc89ge8aprcf'), mobile=AES_ENCRYPT(mobile, 'goprs010058074ea3dc0bc89ge8aprcf'), email=AES_ENCRYPT(email, 'goprs010058074ea3dc0bc89ge8aprcf');

SET @product_id := (SELECT id FROM `t_products` WHERE unicode='410');

SET @flag_id := (SELECT id FROM `t_flags` WHERE name='agent-profile');
SET @priv_id := (select id from `t_privileges` where name='checkbalance' AND flag_id = @flag_id LIMIT 1);
INSERT INTO `t_product_privileges` (`product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (@product_id, @flag_id, @priv_id, 1);


SET @flag_id := (SELECT id FROM `t_flags` WHERE name='agent-fundrequest');
SET @priv_id := (select id from `t_privileges` where name='index' AND flag_id = @flag_id LIMIT 1);
INSERT INTO `t_product_privileges` (`product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (@product_id, @flag_id, @priv_id, 1);
SET @priv_id := (select id from `t_privileges` where name='send' AND flag_id = @flag_id LIMIT 1);
INSERT INTO `t_product_privileges` (`product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (@product_id, @flag_id, @priv_id, 1);
SET @priv_id := (select id from `t_privileges` where name='response' AND flag_id = @flag_id LIMIT 1);
INSERT INTO `t_product_privileges` (`product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (@product_id, @flag_id, @priv_id, 1);


SET @flag_id := (SELECT id FROM `t_flags` WHERE name='agent-reports');
SET @priv_id := (select id from `t_privileges` where name='index' AND flag_id = @flag_id LIMIT 1);
INSERT INTO `t_product_privileges` (`product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (@product_id, @flag_id, @priv_id, 1);
SET @priv_id := (select id from `t_privileges` where name='agentfundrequests' AND flag_id = @flag_id LIMIT 1);
INSERT INTO `t_product_privileges` (`product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (@product_id, @flag_id, @priv_id, 1);

SET @flag_id := (SELECT id FROM `t_flags` WHERE name='agent-index');
SET @priv_id := (select id from `t_privileges` where name='noscript' AND flag_id = @flag_id LIMIT 1);
INSERT INTO `t_product_privileges` (`product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (@product_id, @flag_id, @priv_id, 1);
SET @priv_id := (select id from `t_privileges` where name='nocookie' AND flag_id = @flag_id LIMIT 1);
INSERT INTO `t_product_privileges` (`product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (@product_id, @flag_id, @priv_id, 1);

-------------------------------
SET @product_id := (SELECT id FROM `t_products` WHERE unicode='410');

SET @flag_id := (SELECT id FROM `t_flags` WHERE name='agent-reports');
SET @priv_id := (select id from `t_privileges` where name='exportagentfundrequests' AND flag_id = @flag_id LIMIT 1);
INSERT INTO `t_product_privileges` (`product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (@product_id,  @flag_id, @priv_id, 1);



SET @flag_id := (SELECT id FROM `t_flags` WHERE name='agent-agentfunding');

INSERT INTO `t_privileges` (`id` ,`name` ,`flag_id` ,`description`)
VALUES (NULL , 'requestfund', @flag_id, 'Agent Funding-agent can request for fund');

SET @privilege_id = last_insert_id();
SET @product_id := (SELECT id FROM t_products WHERE unicode = '110' AND status = 'active' LIMIT 1);
Insert into `t_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @product_id, @flag_id, @privilege_id, '1');
SET @product_id := (SELECT id FROM t_products WHERE unicode = '210' AND status = 'active' LIMIT 1);
Insert into `t_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @product_id, @flag_id, @privilege_id, '1');
SET @product_id := (SELECT id FROM t_products WHERE unicode = '410' AND status = 'active' LIMIT 1);
Insert into `t_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @product_id, @flag_id, @privilege_id, '1');


---------------------


SET @flag_id := (SELECT id FROM `t_flags` WHERE name='agent-agentfunding');
INSERT INTO `t_privileges` (`id` ,`name` ,`flag_id` ,`description`)
VALUES (NULL , 'fundrequest', @flag_id, 'Agent should be able to view his fund requests. The requests should also display the status and remarks entered by operation if approved/rejected.');

SET @privilege_id = last_insert_id();
SET @product_id := (SELECT id FROM t_products WHERE unicode = '110' AND status = 'active' LIMIT 1);
Insert into `t_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @product_id, @flag_id, @privilege_id, '1');
SET @product_id := (SELECT id FROM t_products WHERE unicode = '210' AND status = 'active' LIMIT 1);
Insert into `t_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @product_id, @flag_id, @privilege_id, '1');
SET @product_id := (SELECT id FROM t_products WHERE unicode = '410' AND status = 'active' LIMIT 1);
Insert into `t_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @product_id, @flag_id, @privilege_id, '1');

-----------------

SET @product_id := (SELECT id FROM `t_products` WHERE unicode='410');
SET @flag_id := (SELECT id FROM `t_flags` WHERE name='agent-reports');
SET @priv_id := (select id from `t_privileges` where name='exportfeereport' AND flag_id = @flag_id LIMIT 1);
INSERT INTO `t_product_privileges` (`product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (@product_id,  @flag_id, @priv_id, 1);