INSERT INTO `t_products` (`id`, `bank_id`, `name`, `description`, `currency`, `ecs_product_code`, `program_type`, `unicode`, `const`, `flag_common`, `static_otp`, `by_ops_id`, `ip`, `date_created`, `status`) VALUES(NULL, 3, 'RATNAKAR BANK SHMART!PAY PREPAID CARD', 'MVC BASED PREPAID CARD FOR ONLINE PAYMENTS', 'INR', '10000001', 'Mvc', 918, 'RAT_MVC', 'no', 'no', 101, 122160080129, '2014-07-31 12:55:41', 'active');

SET @product_id := (SELECT id FROM t_products WHERE unicode = '918' AND status = 'active' LIMIT 1);

INSERT INTO `purse_master` VALUES (NUll, '3', @product_id, '1', 'RMW918', 'Ratnakar MVC Wallet', 'Ratnakar MVC Wallet', '50000', 'no', 'yes', 'none', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', 'none', 'no', '0', '0', '0', '0', '0', '0', '0', '0', '1', NOW(), NOW(), NOW(), '101', 'active');

SET @purse_master_id := last_insert_id();

INSERT INTO `product_customer_limits` VALUES (NULL, '3', @purse_master_id, 'KYC918', 'kyc', 'KYC Ratnakar MVC', 'KYC Ratnakar MVC', '10000', '1', '10000', '10000', '10000', '120000', '1', '0', '0', '0', '0', NOW(), NOW(), NOW(), '101', 'active');
INSERT INTO `product_customer_limits` VALUES (NULL, '3', @purse_master_id, 'NKC918', 'non-kyc', 'Non-KYC Ratnakar MVC', 'Non KYC Ratnakar MVC', '10000', '1', '10000', '10000', '10000', '120000', '1', '0', '0', '0', '0', NOW(), NOW(), NOW(), '101', 'active');

/*SET @product_id := (SELECT id FROM t_products WHERE unicode = '918' AND status = 'active' LIMIT 1);
SET @flag_id := (SELECT id FROM `t_flags` WHERE name='agent-profile');
SET @prev_id := (SELECT id FROM `t_privileges` WHERE name='checkbalance' AND flag_id=@flag_id);
INSERT INTO `t_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, @product_id, @flag_id, @prev_id, 1);

SET @product_id := (SELECT id FROM t_products WHERE unicode = '918' AND status = 'active' LIMIT 1);
SET @flag_id := (SELECT id FROM `t_flags` WHERE name='agent-fundrequest');
SET @prev_id := (SELECT id FROM `t_privileges` WHERE name='index' and flag_id=@flag_id);
INSERT INTO `t_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, @product_id, @flag_id, @prev_id, 1);
SET @prev_id := (SELECT id FROM `t_privileges` WHERE name='send' and flag_id=@flag_id);
INSERT INTO `t_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, @product_id, @flag_id, @prev_id, 1);
SET @prev_id := (SELECT id FROM `t_privileges` WHERE name='response' and flag_id=@flag_id);
INSERT INTO `t_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, @product_id, @flag_id, @prev_id, 1);

SET @product_id := (SELECT id FROM t_products WHERE unicode = '918' AND status = 'active' LIMIT 1);
SET @flag_id := (SELECT id FROM `t_flags` WHERE name='agent-reports');
SET @prev_id := (SELECT id FROM `t_privileges` WHERE name='index' and flag_id=@flag_id);
INSERT INTO `t_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, @product_id, @flag_id, @prev_id, 1);
SET @prev_id := (SELECT id FROM `t_privileges` WHERE name='exportagentfundrequests' and flag_id=@flag_id);
INSERT INTO `t_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, @product_id, @flag_id, @prev_id, 1);
SET @prev_id := (SELECT id FROM `t_privileges` WHERE name='exportcommreport' and flag_id=@flag_id);
INSERT INTO `t_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, @product_id, @flag_id, @prev_id, 1);
SET @prev_id := (SELECT id FROM `t_privileges` WHERE name='agentsummary' and flag_id=@flag_id);
INSERT INTO `t_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, @product_id, @flag_id, @prev_id, 1);
SET @prev_id := (SELECT id FROM `t_privileges` WHERE name='exportagentsummary' and flag_id=@flag_id);
INSERT INTO `t_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, @product_id, @flag_id, @prev_id, 1);
SET @prev_id := (SELECT id FROM `t_privileges` WHERE name='agentcommissionsummary' and flag_id=@flag_id);
INSERT INTO `t_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, @product_id, @flag_id, @prev_id, 1);
SET @prev_id := (SELECT id FROM `t_privileges` WHERE name='agentfundrequests' and flag_id=@flag_id);
INSERT INTO `t_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, @product_id, @flag_id, @prev_id, 1);
SET @prev_id := (SELECT id FROM `t_privileges` WHERE name='agentfundrequests' and flag_id=@flag_id);
INSERT INTO `t_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, @product_id, @flag_id, @prev_id, 1);
SET @prev_id := (SELECT id FROM `t_privileges` WHERE name='exportagentcommissionsummary' and flag_id=@flag_id);
INSERT INTO `t_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, @product_id, @flag_id, @prev_id, 1);
SET @prev_id := (SELECT id FROM `t_privileges` WHERE name='exportagentcommissionsummary' and flag_id=@flag_id);
INSERT INTO `t_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, @product_id, @flag_id, @prev_id, 1);

SET @product_id := (SELECT id FROM t_products WHERE unicode = '918' AND status = 'active' LIMIT 1);
SET @flag_id := (SELECT id FROM `t_flags` WHERE name='agent-index');
SET @prev_id := (SELECT id FROM `t_privileges` WHERE name='noscript' and flag_id=@flag_id);
INSERT INTO `t_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, @product_id, @flag_id, @prev_id, 1);
SET @prev_id := (SELECT id FROM `t_privileges` WHERE name='nocookie' and flag_id=@flag_id);
INSERT INTO `t_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, @product_id, @flag_id, @prev_id, 1);

SET @product_id := (SELECT id FROM t_products WHERE unicode = '918' AND status = 'active' LIMIT 1);
SET @flag_id := (SELECT id FROM `t_flags` WHERE name='agent-agentfunding');
SET @prev_id := (SELECT id FROM `t_privileges` WHERE name='index' and flag_id=@flag_id);
INSERT INTO `t_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, @product_id, @flag_id, @prev_id, 1);
SET @prev_id := (SELECT id FROM `t_privileges` WHERE name='requestfund' and flag_id=@flag_id);
INSERT INTO `t_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, @product_id, @flag_id, @prev_id, 1);
SET @prev_id := (SELECT id FROM `t_privileges` WHERE name='viewfundrequest' and flag_id=@flag_id);
INSERT INTO `t_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, @product_id, @flag_id, @prev_id, 1);
SET @prev_id := (SELECT id FROM `t_privileges` WHERE name='fundrequest' and flag_id=@flag_id);
INSERT INTO `t_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, @product_id, @flag_id, @prev_id, 1);
*/

INSERT INTO `t_unicode_conf` (`unicode_initials`, `bank_unicode`, `product_unicode`) VALUES ('30091800', '300', '918');


--
-- Table structure for table `rat_mvc_cardholders_status`
--

CREATE TABLE IF NOT EXISTS `rat_mvc_cardholders_status` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cardholder_id` int(11) NOT NULL,
  `mvc_type` enum('mvcc','mvci') NOT NULL,
  `device_id` varchar(30) DEFAULT NULL,
  `mvc_enroll_status` enum('new','pending','success','failed') NOT NULL,
  `mvc_enroll_attempts` int(3) NOT NULL DEFAULT '0',
  `date_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;


-- --------------------------------------------------------

--
-- Table structure for table `rat_mvc_cardholder_details`
--

CREATE TABLE IF NOT EXISTS `rat_mvc_cardholder_details` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cardholder_id` int(11) NOT NULL,
  `crn` varchar(30) NOT NULL,
  `unicode` bigint(20) unsigned NOT NULL,
  `email` varchar(100) NOT NULL,
  `title` enum('mr','mrs','ms','dr','chief','miss','sir') NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `middle_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `mobile_country_code` varchar(6) NOT NULL,
  `mobile_number` varchar(15) NOT NULL,
  `arn` varchar(10) CHARACTER SET utf8 DEFAULT NULL,
  `alternate_contact_number` varchar(20) CHARACTER SET utf8 NOT NULL,
  `father_first_name` varchar(20) CHARACTER SET utf8 NOT NULL,
  `father_middle_name` varchar(20) CHARACTER SET utf8 NOT NULL,
  `father_last_name` varchar(20) CHARACTER SET utf8 NOT NULL,
  `mother_maiden_name` varchar(20) CHARACTER SET utf8 NOT NULL,
  `spouse_first_name` varchar(20) CHARACTER SET utf8 NOT NULL,
  `spouse_middle_name` varchar(20) CHARACTER SET utf8 NOT NULL,
  `spouse_last_name` varchar(20) CHARACTER SET utf8 NOT NULL,
  `res_type` enum('owned','rented','parental') CHARACTER SET utf8 NOT NULL,
  `nationality` varchar(30) CHARACTER SET utf8 NOT NULL,
  `date_of_birth` date NOT NULL,
  `gender` enum('male','female') CHARACTER SET utf8 NOT NULL,
  `flat_number` varchar(12) CHARACTER SET utf8 NOT NULL,
  `address_line1` varchar(100) CHARACTER SET utf8 NOT NULL,
  `address_line2` varchar(100) CHARACTER SET utf8 NOT NULL,
  `city` varchar(50) CHARACTER SET utf8 NOT NULL,
  `taluka` varchar(50) CHARACTER SET utf8 NOT NULL,
  `district` varchar(50) CHARACTER SET utf8 NOT NULL,
  `state` varchar(50) CHARACTER SET utf8 NOT NULL,
  `country` varchar(50) CHARACTER SET utf8 NOT NULL,
  `pincode` int(10) NOT NULL,
  `landmark` varchar(150) CHARACTER SET utf8 NOT NULL,
  `customer_mvc_type` enum('mvcc','mvci') CHARACTER SET utf8 NOT NULL,
  `device_id` varchar(30) CHARACTER SET utf8 NOT NULL,
  `caste_category` varchar(10) CHARACTER SET utf8 DEFAULT NULL,
  `profession` varchar(25) CHARACTER SET utf8 DEFAULT NULL,
  `annual_income` int(15) DEFAULT NULL,
  `pan_number` varchar(15) CHARACTER SET utf8 DEFAULT NULL,
  `nominee_first_name` varchar(20) CHARACTER SET utf8 DEFAULT NULL,
  `nominee_middle_name` varchar(20) CHARACTER SET utf8 DEFAULT NULL,
  `nominee_last_name` varchar(20) CHARACTER SET utf8 DEFAULT NULL,
  `date_of_birth_nominee` date DEFAULT NULL,
  `relationship_with_applicant` varchar(20) CHARACTER SET utf8 DEFAULT NULL,
  `declaration` varchar(10) CHARACTER SET utf8 DEFAULT NULL,
  `place` varchar(30) CHARACTER SET utf8 DEFAULT NULL,
  `introducer_first_name` varchar(20) CHARACTER SET utf8 DEFAULT NULL,
  `introducer_middle_name` varchar(20) CHARACTER SET utf8 DEFAULT NULL,
  `introducer_last_name` varchar(20) CHARACTER SET utf8 DEFAULT NULL,
  `introducer_account_number` varchar(30) CHARACTER SET utf8 DEFAULT NULL,
  `introducer_branch` varchar(30) CHARACTER SET utf8 DEFAULT NULL,
  `introducer_know_since` date DEFAULT NULL,
  `id_proof_attached` enum('yes','no') CHARACTER SET utf8 DEFAULT NULL,
  `address_proof_attached` enum('yes','no') CHARACTER SET utf8 DEFAULT NULL,
  `uid_number` varchar(30) CHARACTER SET utf8 DEFAULT NULL,
  `already_bank_account` enum('yes','no') CHARACTER SET utf8 DEFAULT NULL,
  `vehicle_type` varchar(20) CHARACTER SET utf8 DEFAULT NULL,
  `educational_qualifications` varchar(100) CHARACTER SET utf8 DEFAULT NULL,
  `family_members` int(4) DEFAULT NULL,
  `date_created` datetime DEFAULT NULL,
  `shmart_rewards` enum('yes','no') NOT NULL,
  `products_acknowledgement` enum('yes','no') DEFAULT NULL,
  `rewards_acknowledgement` enum('yes','no') DEFAULT NULL,
  `by_ops_id` int(11) unsigned DEFAULT NULL,
  `by_agent_id` int(11) unsigned DEFAULT NULL,
  `ip` bigint(20) unsigned NOT NULL,
  `status` enum('active','inactive') NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `rat_mvc_cardholder_offers`
--

CREATE TABLE IF NOT EXISTS `rat_mvc_cardholder_offers` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `cardholder_id` int(11) unsigned NOT NULL,
  `is_book` enum('yes','no') CHARACTER SET utf8 NOT NULL,
  `is_travel` enum('yes','no') CHARACTER SET utf8 NOT NULL,
  `is_movies` enum('yes','no') CHARACTER SET utf8 NOT NULL,
  `is_shopping` enum('yes','no') CHARACTER SET utf8 NOT NULL,
  `is_electronics` enum('yes','no') CHARACTER SET utf8 NOT NULL,
  `is_music` enum('yes','no') CHARACTER SET utf8 NOT NULL,
  `is_automobiles` enum('yes','no') CHARACTER SET utf8 NOT NULL,
  `date_created` datetime NOT NULL,
  `status` enum('active','inactive') CHARACTER SET utf8 NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------


/*SET @product_id := (SELECT id FROM `t_products` WHERE unicode='918');
INSERT INTO `t_flags` (`id`, `name`, `description`, `active_on_dev`, `active_on_prod`) VALUES(NULL, 'agent-mvc_ratnakar_cardholder', 'Add CardHolders', 1, 0);
SET @flag_id := last_insert_id();
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES(NULL, 'step1', @flag_id, 'Cardholder Enrollment step 1');
SET @prev_id := last_insert_id();
INSERT INTO `t_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, @product_id, @flag_id, @prev_id, 1);
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES(NULL, 'step2', @flag_id, 'Cardholder Enrollment step 2');
SET @prev_id := last_insert_id();
INSERT INTO `t_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, @product_id, @flag_id, @prev_id, 1);
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES(NULL, 'step3', @flag_id, 'Cardholder Enrollment step 3');
SET @prev_id := last_insert_id();
INSERT INTO `t_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, @product_id, @flag_id, @prev_id, 1);
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES(NULL, 'ecsregistration', @flag_id, 'Cardholder Registration with ECS');
SET @prev_id := last_insert_id();
INSERT INTO `t_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, @product_id, @flag_id, @prev_id, 1);
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES(NULL, 'complete', @flag_id, 'Cardholder Registration with MVC');
SET @prev_id := last_insert_id();
INSERT INTO `t_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, @product_id, @flag_id, @prev_id, 1);


SET @product_id := (SELECT id FROM `t_products` WHERE unicode='918');
INSERT INTO `t_flags` (`id`, `name`, `description`, `active_on_dev`, `active_on_prod`) VALUES(NULL, 'agent-mvc_ratnakar_loadbalance', 'Add CardHolders - CardLoad', 1, 0);
SET @flag_id := last_insert_id();
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES(NULL, 'index', @flag_id, 'Cardholder Cardload');
SET @prev_id := last_insert_id();
INSERT INTO `t_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, @product_id, @flag_id, @prev_id, 1);


SET @product_id := (SELECT id FROM `t_products` WHERE unicode='918');
INSERT INTO `t_flags` (`id`, `name`, `description`, `active_on_dev`, `active_on_prod`) VALUES(NULL, 'agent-mvc_ratnakar_cardholderfund', 'CardReLoad', 1, 0);
SET @flag_id := last_insert_id();
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES(NULL, 'mobile', @flag_id, 'Cardholder Cardreload - Enter Mobile');
SET @prev_id := last_insert_id();
INSERT INTO `t_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, @product_id, @flag_id, @prev_id, 1);
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES(NULL, 'load',  @flag_id, 'Cardholder Cardreload - Enter Load amount ');
SET @prev_id := last_insert_id();
INSERT INTO `t_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, @product_id, @flag_id, @prev_id, 1);
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES(NULL, 'complete', @flag_id, 'Cardholder Cardreload - Success');
SET @prev_id := last_insert_id();
INSERT INTO `t_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, @product_id, @flag_id, @prev_id, 1);
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES(NULL, 'cancel', @flag_id, 'Cancel');
SET @prev_id := last_insert_id();
INSERT INTO `t_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, @product_id, @flag_id, @prev_id, 1);



SET @product_id := (SELECT id FROM `t_products` WHERE unicode='918');
INSERT INTO `t_flags` (`id`, `name`, `description`, `active_on_dev`, `active_on_prod`) VALUES(NULL, 'agent-mvc_ratnakar_reports', 'MVC Ratnakar Reports', 1, 0);
SET @flag_id := last_insert_id();
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES(NULL, 'agentwiseload', @flag_id, 'Load report of Agent');
SET @prev_id := last_insert_id();
INSERT INTO `t_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, @product_id, @flag_id, @prev_id, 1);
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES(NULL, 'cardholderactivations', @flag_id, 'Cardholder Activations of Agent');
SET @prev_id := last_insert_id();
INSERT INTO `t_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, @product_id, @flag_id, @prev_id, 1);
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES(NULL, 'index', @flag_id, 'Index of reports section');
SET @prev_id := last_insert_id();
INSERT INTO `t_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, @product_id, @flag_id, @prev_id, 1);
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES(NULL, 'exportagentwiseload', @flag_id, 'Agent Load/Reload Report');
SET @prev_id := last_insert_id();
INSERT INTO `t_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, @product_id, @flag_id, @prev_id, 1);
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES(NULL, 'exportcardholderactivations', @flag_id, 'Agent Cardholder Activations Report');
SET @prev_id := last_insert_id();
INSERT INTO `t_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, @product_id, @flag_id, @prev_id, 1);
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES(NULL, 'loadreloadcomm', @flag_id, 'Load Reload commission Report');
SET @prev_id := last_insert_id();
INSERT INTO `t_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, @product_id, @flag_id, @prev_id, 1);
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES(NULL, 'exportloadreloadcomm', @flag_id, 'Export Load Reload commission Report');
SET @prev_id := last_insert_id();
INSERT INTO `t_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, @product_id, @flag_id, @prev_id, 1);
*/

ALTER TABLE `rat_corp_cardholders` CHANGE `status` `status` ENUM( 'active', 'inactive', 'ecs_pending', 'ecs_failed', 'blocked', 'activation_pending', 'incomplete' );
ALTER TABLE `rat_mvc_cardholder_details` CHANGE `status` `status` ENUM( 'active', 'inactive', 'incomplete' );

UPDATE `t_products` SET `flag_common` = 'yes', `program_type` = 'DigiWallet' WHERE unicode = '918' AND status = 'active';

ALTER TABLE `purse_master` ADD `allow_debit` ENUM( 'yes', 'no' ) NOT NULL DEFAULT 'no' AFTER `allow_remit`;

UPDATE `purse_master` SET `allow_debit` = 'yes' WHERE `code` ='RMW918';
UPDATE `purse_master` SET `allow_debit` = 'yes' WHERE `code` ='PAY916';

ALTER TABLE `rat_mvc_cardholder_details` ADD `product_id` INT( 11 ) UNSIGNED NOT NULL AFTER `id`;
ALTER TABLE `rat_mvc_cardholder_details` ADD `landline` VARCHAR( 15 ) NOT NULL AFTER `flat_number`;
ALTER TABLE `rat_mvc_cardholder_details` CHANGE `already_bank_account` `already_bank_account` ENUM( 'y', 'n' ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;

INSERT INTO `api_user` (`id`, `tp_user_id`, `username`, `password`, `status`, `date_created`) VALUES('8', '8', 'rblmvcuser', '3c916eb0d4150e054e6dd49e4f11624a11637a78', 'active', NOW());
INSERT INTO `api_user_ip` (`id`, `tp_user_id`,`tp_user_ip`) VALUES ('8', '8','127.0.0.1,122.160.80.129');



















