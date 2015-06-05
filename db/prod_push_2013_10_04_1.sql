/*

Code not pushed on production 

*/

ALTER TABLE `hic_cardholders` CHANGE `emp_id` `employee_id` VARCHAR( 10 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL ,
CHANGE `status` `status` ENUM( 'active', 'inactive', 'incomplete' ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT 'incomplete';

ALTER TABLE `hic_cardholder_details` CHANGE `emp_id` `employee_id` VARCHAR( 10 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL ,
CHANGE `status` `status` ENUM( 'active', 'inactive', 'incomplete' ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT 'incomplete';

SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-hic_ratnakar_cardholder');

INSERT INTO `t_privileges` (`name`, `flag_id`, `description`) VALUES ('searchcardholders', @flag_id, 'Search CardHolders');

CREATE TABLE IF NOT EXISTS `log_hic_hospital` (
  `hospital_id` int(11) unsigned NOT NULL,
  `hospital_id_code` int(11) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `address` varchar(255) NOT NULL,
  `city` varchar(50) DEFAULT NULL,
  `state` varchar(50) DEFAULT NULL,
  `pincode` int(11) unsigned DEFAULT NULL,
  `std_code` varchar(10) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `status` enum('active','inactive','deleted') DEFAULT 'active',
  `by_agent_id` int(11) unsigned NOT NULL,
  `ip` varchar(16) DEFAULT NULL,
  `date_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `log_hic_terminal`
--

CREATE TABLE IF NOT EXISTS `log_hic_terminal` (
  `terminal_id` int(11) unsigned NOT NULL,
  `hospital_id` int(11) unsigned NOT NULL,
  `terminal_id_code` bigint(16) unsigned NOT NULL,
  `status` enum('active','inactive','deleted') NOT NULL DEFAULT 'active',
  `by_agent_id` int(11) DEFAULT NULL,
  `ip` varchar(16) DEFAULT NULL,
  `date_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



SET @flag_id := (SELECT id FROM `t_flags` WHERE name='agent-hic_ratnakar_hospital');
SET @product_id := (SELECT id FROM `t_products` WHERE name='MEDI ASSIST CARD');

INSERT INTO `t_privileges` (`name`, `flag_id`, `description`) VALUES ('delete', @flag_id, 'Delete Hospital');
SET @privilege_id = last_insert_id();
INSERT INTO `t_product_privileges` (`product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (@product_id, @flag_id, @privilege_id, 1);

ALTER TABLE `hic_cardholders` ADD `by_ops_id` INT( 11 ) UNSIGNED NOT NULL AFTER `corporate_id` ,
ADD `date_created` DATETIME NOT NULL AFTER `by_ops_id`;
ALTER TABLE `hic_cardholder_details` ADD `ip` BIGINT( 20 ) UNSIGNED NOT NULL AFTER `corporate_id` ,
ADD `by_ops_id` INT( 11 ) UNSIGNED NOT NULL AFTER `ip` ,
ADD `date_created` DATETIME NOT NULL AFTER `by_ops_id`;
ALTER TABLE `hic_cardholder_details` CHANGE `email` `email` VARCHAR(100) NOT NULL, CHANGE `employer_name` `employer_name` VARCHAR(100) NOT NULL, CHANGE `corporate_id` `corporate_id` VARCHAR(11) NOT NULL;
ALTER TABLE `hic_cardholders` CHANGE `email` `email` VARCHAR(100) NOT NULL, CHANGE `employer_name` `employer_name` VARCHAR(100) NOT NULL, CHANGE `corporate_id` `corporate_id` VARCHAR(11) NOT NULL;
ALTER TABLE `hic_cardholders` CHANGE `date_created` `date_created` TIMESTAMP NULL DEFAULT NULL;
ALTER TABLE `hic_cardholder_details` CHANGE `date_created` `date_created` TIMESTAMP NULL DEFAULT NULL ;
ALTER TABLE `hic_cardholders` ADD `date_updated` TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `date_created`;
ALTER TABLE `hic_cardholder_details` ADD `date_updated` TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `date_created`;

SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-hic_ratnakar_cardholder');

INSERT INTO `t_privileges` (`name`, `flag_id`, `description`) VALUES ('view', @flag_id, 'View CardHolder details');
ALTER TABLE `hic_cardholders`  ADD `batch_name` VARCHAR(100) NOT NULL AFTER `corporate_id`;

CREATE TABLE IF NOT EXISTS `corporate_master` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ecs_corp_id` int(11) unsigned NOT NULL,
  `name` varchar(100) NOT NULL,
  `address` varchar(255) NOT NULL,
  `city` varchar(100) NOT NULL,
  `state` varchar(100) NOT NULL,
  `pincode` varchar(10) NOT NULL,
  `contact_number` varchar(100) NOT NULL,
  `contact_email` varchar(100) NOT NULL,
  `by_ops_id` int(11) unsigned NOT NULL,
  `ip` varchar(16) NOT NULL,
  `date_created` timestamp NULL DEFAULT NULL,
  `date_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;


CREATE TABLE IF NOT EXISTS `hic_insurance_claim` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int(11) unsigned NOT NULL,
  `customer_id` int(11) unsigned NOT NULL,
  `cardholder_id` int(11) unsigned NOT NULL,
  `amount` decimal(11,2) NOT NULL,
  `hospital_id_code` int(11) unsigned NOT NULL,
  `txn_type` char(4) NOT NULL,
  `txn_code` int(11) unsigned NOT NULL,
  `by_agent_id` int(11) unsigned NOT NULL,
  `ip` varchar(16) NOT NULL,
  `date_created` timestamp NULL DEFAULT NULL,
  `date_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `num_fail_loads` tinyint(4) NOT NULL DEFAULT '0',
  `status` enum('pending','loaded','failed','cutoff') NOT NULL DEFAULT 'pending',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;


INSERT INTO `t_flags` (`id`, `name`, `description`, `active_on_dev`, `active_on_prod`) VALUES (NULL, 'operation-corporate', 'Manage Corporates', '1', '0');
SET @flag_id = last_insert_id();
INSERT INTO `t_privileges` (`name`, `flag_id`, `description`) VALUES ('index',@flag_id , 'Corporates Listing');
INSERT INTO `t_privileges` (`name`, `flag_id`, `description`) VALUES ('add',@flag_id , 'Add Corporates');
INSERT INTO `t_privileges` (`name`, `flag_id`, `description`) VALUES ('edit',@flag_id , 'Edit Corporates');
INSERT INTO `t_privileges` (`name`, `flag_id`, `description`) VALUES ('delete',@flag_id , 'Delete Corporates');

CREATE  TABLE `log_corporate_master` (  `corporate_id` int( 11  )  unsigned NOT  NULL,
 `ecs_corp_id` int( 11  )  unsigned NOT  NULL ,
 `name` varchar( 100  )  NOT  NULL ,
 `address` varchar( 255  )  NOT  NULL ,
 `city` varchar( 100  )  NOT  NULL ,
 `state` varchar( 100  )  NOT  NULL ,
 `pincode` varchar( 10  )  NOT  NULL ,
 `contact_number` varchar( 100  )  NOT  NULL ,
 `contact_email` varchar( 100  )  NOT  NULL ,
 `by_ops_id` int( 11  )  unsigned NOT  NULL ,
 `ip` varchar( 16  )  NOT  NULL ,
 `date_created` timestamp NULL  DEFAULT NULL ,
 `date_updated` timestamp NOT  NULL  DEFAULT CURRENT_TIMESTAMP  ON  UPDATE  CURRENT_TIMESTAMP ,
 `status` enum(  'active',  'inactive'  )  NOT  NULL DEFAULT  'active' ) ENGINE  = InnoDB  DEFAULT CHARSET  = latin1;

SET @flag_id := (SELECT id FROM `t_flags` WHERE name='agent-hic_ratnakar_hospital');
SET @product_id := (SELECT id FROM `t_products` WHERE name='MEDI ASSIST CARD');

INSERT INTO `t_privileges` (`name`, `flag_id`, `description`) VALUES ('edit', @flag_id, 'Edit Hospital');
SET @privilege_id = last_insert_id();
INSERT INTO `t_product_privileges` (`product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (@product_id, @flag_id, @privilege_id, 1);


SET @product_id := (SELECT id FROM `t_products` WHERE name='MEDI ASSIST CARD');

INSERT INTO `t_product_privileges` (`product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (@product_id, '28', '146', 1),
 (@product_id, '33', '173', 1), (@product_id, '35', '193', 1),
 (@product_id, '35', '194', 1), (@product_id, '33', '203', 1),
 (@product_id, '32', '208', 1), (@product_id, '32', '209', 1),
 (@product_id, '32', '210', 1), (@product_id, '33', '224', 1),
 (@product_id, '33', '225', 1), (@product_id, '33', '228', 1),
 (@product_id, '33', '239', 1), (@product_id, '33', '240', 1),
 (@product_id, '33', '253', 1), (@product_id, '33', '254', 1);
 
INSERT INTO `t_flags` (`id`, `name`, `description`, `active_on_dev`, `active_on_prod`) VALUES
(NULL, 'agent-hic_ratnakar_cardload', 'Load Card', 1, 0);
SET @flag_id = last_insert_id();

SET @product_id_val = (SELECT id FROM t_products WHERE name = 'MEDI ASSIST CARD');
INSERT INTO `t_privileges` (`name`, `flag_id`, `description`) VALUES ('search', @flag_id, 'Search HIC Cardholder');
SET @privilege_index_id = last_insert_id();
INSERT INTO `t_privileges` (`name`, `flag_id`, `description`) VALUES ('load', @flag_id, 'Load HIC Cardholder');
SET @privilege_search_id = last_insert_id();

INSERT INTO `t_product_privileges` (`product_id`, `flag_id`, `privilege_id`, `allow`) VALUES
(@product_id_val, @flag_id, @privilege_index_id, 1),
(@product_id_val, @flag_id, @privilege_search_id, 1);

CREATE TABLE IF NOT EXISTS `rat_hic_insurance_claim` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int(11) unsigned NOT NULL,
  `customer_id` int(11) unsigned NOT NULL,
  `cardholder_id` int(11) unsigned NOT NULL,
  `amount` decimal(11,2) NOT NULL,
  `hospital_id_code` int(11) unsigned NOT NULL,
  `txn_type` char(4) NOT NULL,
  `txn_code` int(11) unsigned NOT NULL,
  `by_agent_id` int(11) unsigned NOT NULL,
  `ip` varchar(16) NOT NULL,
  `date_created` timestamp NULL DEFAULT NULL,
  `date_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `num_fail_loads` tinyint(4) NOT NULL DEFAULT '0',
  `status` enum('pending','loaded','failed','cutoff') NOT NULL DEFAULT 'pending',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

ALTER TABLE hic_hospital RENAME TO rat_hic_hospital;
ALTER TABLE hic_terminal RENAME TO rat_hic_terminal;
ALTER TABLE log_hic_hospital RENAME TO log_rat_hic_hospital;
ALTER TABLE log_hic_terminal RENAME TO log_rat_hic_terminal;
ALTER TABLE hic_cardholders RENAME TO rat_hic_cardholders;
ALTER TABLE hic_cardholder_details RENAME TO rat_hic_cardholder_details;

SET @flag_id := (SELECT id FROM `t_flags` WHERE name='operation-hic_ratnakar_cardholder');
UPDATE `t_privileges` SET name = 'uploadcardholders' WHERE name = 'uploadcardholder' AND flag_id = @flag_id;
-- Missed to add customer_master_id added twice in detail page - FIXED
ALTER TABLE `rat_hic_cardholders` ADD `customer_master_id` INT( 11 ) NOT NULL AFTER `id`;
ALTER TABLE `rat_hic_cardholders` ADD `product_id` INT( 11 ) NOT NULL AFTER `customer_master_id`;
ALTER TABLE `rat_hic_cardholder_details` ADD `product_id` INT( 11 ) NOT NULL AFTER `id`;

ALTER TABLE `rat_hic_cardholders` ADD `upload_status` ENUM( 'temp', 'pass', 'duplicate' ) NOT NULL DEFAULT 'temp' AFTER `date_updated`;

ALTER TABLE `rat_hic_cardholder_details` ADD `customer_master_id` INT( 11 ) NOT NULL AFTER `id`;

ALTER TABLE  `rat_hic_cardholders` ADD  `by_agent_id` INT( 11 ) UNSIGNED NOT NULL AFTER  `by_ops_id`;

INSERT INTO `t_flags` (`name`, `description`, `active_on_dev`, `active_on_prod`) VALUES ('agent-hic_ratnakar_cardholder', 'Cardholder of ratnakar bank hic', '1', '0');
SET @flag_id = last_insert_id();
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'add', @flag_id, 'Add Cardholder');
SET @privilege_id = last_insert_id();
SET @product_id := (SELECT id from t_products where name='MEDI ASSIST CARD');
INSERT INTO `t_product_privileges` (`id`, `product_id`, `flag_id`, privilege_id, `allow`) VALUES (NULL, @product_id, @flag_id, @privilege_id, 1);

update t_flags set name='agent-corp_ratnakar_cardholder' where name='agent-hic_ratnakar_cardholder' limit 1;
update t_flags set name='agent-corp_ratnakar_hospital' where name='agent-hic_ratnakar_hospital' limit 1;
update t_flags set name='operation-corp_ratnakar_cardholder' where name='operation-hic_ratnakar_cardholder' limit 1;
update t_flags set name='agent-corp_ratnakar_cardload' where name='agent-hic_ratnakar_cardload' limit 1;
ALTER TABLE  `rat_hic_cardholders` ADD  `aadhaar_no` VARCHAR( 20 ) NULL DEFAULT NULL AFTER  `last_name` ,
ADD  `pan` VARCHAR( 10 ) NULL DEFAULT NULL AFTER  `aadhaar_no`;

SET @flag_id := (SELECT id FROM `t_flags` WHERE name='agent-corp_ratnakar_cardholder');
INSERT INTO `t_privileges` (`name`, `flag_id`, `description`) VALUES ('search', @flag_id, 'Search Corporate Cardholder');
SET @privilege_id = last_insert_id(); 
SET @product_id := (SELECT id FROM `t_products` WHERE name='MEDI ASSIST CARD');
INSERT INTO `t_product_privileges` (`product_id`, `flag_id`, `privilege_id`, `allow`) VALUES
(@product_id, @flag_id, @privilege_id, 1);
INSERT INTO `t_privileges` (`name`, `flag_id`, `description`) VALUES ('active', @flag_id, 'Activate Corporate Cardholder');
SET @privilege_id = last_insert_id(); 
INSERT INTO `t_product_privileges` (`product_id`, `flag_id`, `privilege_id`, `allow`) VALUES
(@product_id, @flag_id, @privilege_id, 1);

-- FIXED removed DB name from table name
UPDATE `t_products` SET `program_type` = 'Corp' WHERE `t_products`.`name` ='MEDI ASSIST CARD';
RENAME TABLE `rat_hic_cardholders` TO `rat_corp_cardholders` ;
RENAME TABLE `rat_hic_cardholder_details` TO `rat_corp_cardholder_details` ;
RENAME TABLE `rat_hic_hospital` TO `rat_corp_hospital` ;
RENAME TABLE `rat_hic_insurance_claim` TO `rat_corp_insurance_claim` ;
RENAME TABLE `rat_hic_terminal` TO `rat_corp_terminal` ;
RENAME TABLE `hic_insurance_claim` TO `corp_insurance_claim` ;
RENAME TABLE `log_rat_hic_hospital` TO `log_rat_corp_hospital` ;
RENAME TABLE `log_rat_hic_terminal` TO `log_rat_corp_terminal` ;

SET @flag_id := (SELECT id FROM `t_flags` WHERE name='agent-corp_ratnakar_cardholder');
INSERT INTO `t_privileges` (`name`, `flag_id`, `description`) VALUES ('inactive', @flag_id, 'Deactivate Corporate Cardholder');
SET @privilege_id = last_insert_id(); 
SET @product_id := (SELECT id FROM `t_products` WHERE name='MEDI ASSIST CARD');
INSERT INTO `t_product_privileges` (`product_id`, `flag_id`, `privilege_id`, `allow`) VALUES
(@product_id, @flag_id, @privilege_id, 1);


SET @section_id_val := (SELECT id FROM `t_settings_sections` WHERE name='Program Type');
INSERT INTO `t_settings` (`id`, `settings_section_id`, `name`, `description`, `value`, `currency`, `by_ops_id`, `ip`, `type`, `date_created`, `status`) VALUES
(NULL, @section_id_val, 'HIC', 'Healthcare Insurance Claim', 'Hic', NULL, 101, '127000000001', 'Hic', '2013-07-01 08:02:19', 'active');

INSERT INTO `t_bank` (`id`, `name`, `ifsc_code`, `city`, `branch_name`, `address`, `unicode`, `logo`, `by_ops_id`, `ip`, `date_created`, `status`) VALUES 
(NULL, 'THE RATNAKAR BANK LTD', 'RATN0000068', 'MUMBAI', 'VILE PARLE - MUMBAI', '6, GROUND FLOOR, GALAXY ARCADE, 10, M.G.ROAD, VILEPARLE', 300, NULL, 101, 127000000001, '2013-07-01 07:52:07', 'active');
SET @bank_id_val = last_insert_id();

INSERT INTO `t_log_bank` (`bank_id`, `name`, `ifsc_code`, `city`, `branch_name`, `address`, `unicode`, `by_ops_id`, `ip`, `date_created`, `status`) VALUES
(@bank_id_val, 'THE RATNAKAR BANK LTD', 'RATN0000068', 'MUMBAI', 'VILE PARLE - MUMBAI', '6, GROUND FLOOR, GALAXY ARCADE, 10, M.G.ROAD, VILEPARLE', 300d, 101, '127000000001', '2013-07-01 07:52:07', 'active');

INSERT INTO `t_products` (`id`, `bank_id`, `name`, `description`, `currency`, `ecs_product_code`, `program_type`, `unicode`, `by_ops_id`, `ip`, `date_created`, `status`) VALUES
(NULL, @bank_id_val, 'MEDI ASSIST CARD', 'CO-BRAND PREPAID CARD BY THE RATNAKAR BANK LIMITED AND MEDI ASSIST.', 'INR', '10000025', 'Hic', 310, 101, 127000000001, '2013-07-01 08:13:12', 'active');
SET @product_id_val = last_insert_id();

INSERT INTO `t_log_products` (`product_id`, `bank_id`, `name`, `description`, `currency`, `ecs_product_code`, `program_type`, `unicode`, `by_ops_id`, `ip`, `date_created`, `status`) VALUES
(@product_id_val, @bank_id_val, 'MEDI ASSIST CARD', 'CO-BRAND PREPAID CARD BY THE RATNAKAR BANK LIMITED AND MEDI ASSIST.', 'INR', '10000025', 'Hic', 310, 101, 127000000001, '2013-07-01 08:13:12', 'active');

INSERT INTO `t_flags` (`id`, `name`, `description`, `active_on_dev`, `active_on_prod`) VALUES
(NULL, 'agent-hic_ratnakar_hospital', 'Manage Hospitals', 1, 0);
SET @flag_id = last_insert_id();

INSERT INTO `t_privileges` (`name`, `flag_id`, `description`) VALUES ('index', @flag_id, 'Manage Hospital');
SET @privilege_index_id = last_insert_id();
INSERT INTO `t_privileges` (`name`, `flag_id`, `description`) VALUES ('search', @flag_id, 'Search Hospital');
SET @privilege_search_id = last_insert_id();

INSERT INTO `t_product_privileges` (`product_id`, `flag_id`, `privilege_id`, `allow`) VALUES
(@product_id_val, @flag_id, @privilege_index_id, 1),
(@product_id_val, @flag_id, @privilege_search_id, 1);

INSERT INTO `t_privileges` (`name`, `flag_id`, `description`) VALUES ('add', @flag_id, 'Add Hospital');
SET @privilege_id = last_insert_id();
INSERT INTO `t_product_privileges` (`product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (@product_id_val, @flag_id, @privilege_id, 1);


ALTER TABLE `hic_cardholders` CHANGE `emp_id` `employee_id` VARCHAR( 10 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL ,
CHANGE `status` `status` ENUM( 'active', 'inactive', 'incomplete' ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT 'incomplete';

ALTER TABLE `hic_cardholder_details` CHANGE `emp_id` `employee_id` VARCHAR( 10 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL ,
CHANGE `status` `status` ENUM( 'active', 'inactive', 'incomplete' ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT 'incomplete';

SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-hic_ratnakar_cardholder');

INSERT INTO `t_privileges` (`name`, `flag_id`, `description`) VALUES ('searchcardholders', @flag_id, 'Search CardHolders');



CREATE TABLE IF NOT EXISTS `log_hic_hospital` (
  `hospital_id` int(11) unsigned NOT NULL,
  `hospital_id_code` int(11) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `address` varchar(255) NOT NULL,
  `city` varchar(50) DEFAULT NULL,
  `state` varchar(50) DEFAULT NULL,
  `pincode` int(11) unsigned DEFAULT NULL,
  `std_code` varchar(10) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `status` enum('active','inactive','deleted') DEFAULT 'active',
  `by_agent_id` int(11) unsigned NOT NULL,
  `ip` varchar(16) DEFAULT NULL,
  `date_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `log_hic_terminal`
--

CREATE TABLE IF NOT EXISTS `log_hic_terminal` (
  `terminal_id` int(11) unsigned NOT NULL,
  `hospital_id` int(11) unsigned NOT NULL,
  `terminal_id_code` bigint(16) unsigned NOT NULL,
  `status` enum('active','inactive','deleted') NOT NULL DEFAULT 'active',
  `by_agent_id` int(11) DEFAULT NULL,
  `ip` varchar(16) DEFAULT NULL,
  `date_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



SET @flag_id := (SELECT id FROM `t_flags` WHERE name='agent-hic_ratnakar_hospital');
SET @product_id := (SELECT id FROM `t_products` WHERE name='MEDI ASSIST CARD');

INSERT INTO `t_privileges` (`name`, `flag_id`, `description`) VALUES ('delete', @flag_id, 'Delete Hospital');
SET @privilege_id = last_insert_id();
INSERT INTO `t_product_privileges` (`product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (@product_id, @flag_id, @privilege_id, 1);



ALTER TABLE `hic_cardholders` ADD `by_ops_id` INT( 11 ) UNSIGNED NOT NULL AFTER `corporate_id` ,
ADD `date_created` DATETIME NOT NULL AFTER `by_ops_id`;
ALTER TABLE `hic_cardholder_details` ADD `ip` BIGINT( 20 ) UNSIGNED NOT NULL AFTER `corporate_id` ,
ADD `by_ops_id` INT( 11 ) UNSIGNED NOT NULL AFTER `ip` ,
ADD `date_created` DATETIME NOT NULL AFTER `by_ops_id`;
ALTER TABLE `hic_cardholder_details` CHANGE `email` `email` VARCHAR(100) NOT NULL, CHANGE `employer_name` `employer_name` VARCHAR(100) NOT NULL, CHANGE `corporate_id` `corporate_id` VARCHAR(11) NOT NULL;
ALTER TABLE `hic_cardholders` CHANGE `email` `email` VARCHAR(100) NOT NULL, CHANGE `employer_name` `employer_name` VARCHAR(100) NOT NULL, CHANGE `corporate_id` `corporate_id` VARCHAR(11) NOT NULL;
ALTER TABLE `hic_cardholders` CHANGE `date_created` `date_created` TIMESTAMP NULL DEFAULT NULL;
ALTER TABLE `hic_cardholder_details` CHANGE `date_created` `date_created` TIMESTAMP NULL DEFAULT NULL ;
ALTER TABLE `hic_cardholders` ADD `date_updated` TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `date_created`;
ALTER TABLE `hic_cardholder_details` ADD `date_updated` TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `date_created`;

SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-hic_ratnakar_cardholder');

INSERT INTO `t_privileges` (`name`, `flag_id`, `description`) VALUES ('view', @flag_id, 'View CardHolder details');
ALTER TABLE `hic_cardholders`  ADD `batch_name` VARCHAR(100) NOT NULL AFTER `corporate_id`;

CREATE TABLE IF NOT EXISTS `corporate_master` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ecs_corp_id` int(11) unsigned NOT NULL,
  `name` varchar(100) NOT NULL,
  `address` varchar(255) NOT NULL,
  `city` varchar(100) NOT NULL,
  `state` varchar(100) NOT NULL,
  `pincode` varchar(10) NOT NULL,
  `contact_number` varchar(100) NOT NULL,
  `contact_email` varchar(100) NOT NULL,
  `by_ops_id` int(11) unsigned NOT NULL,
  `ip` varchar(16) NOT NULL,
  `date_created` timestamp NULL DEFAULT NULL,
  `date_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;


CREATE TABLE IF NOT EXISTS `hic_insurance_claim` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int(11) unsigned NOT NULL,
  `customer_id` int(11) unsigned NOT NULL,
  `cardholder_id` int(11) unsigned NOT NULL,
  `amount` decimal(11,2) NOT NULL,
  `hospital_id_code` int(11) unsigned NOT NULL,
  `txn_type` char(4) NOT NULL,
  `txn_code` int(11) unsigned NOT NULL,
  `by_agent_id` int(11) unsigned NOT NULL,
  `ip` varchar(16) NOT NULL,
  `date_created` timestamp NULL DEFAULT NULL,
  `date_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `num_fail_loads` tinyint(4) NOT NULL DEFAULT '0',
  `status` enum('pending','loaded','failed','cutoff') NOT NULL DEFAULT 'pending',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

 


 CREATE  TABLE `log_corporate_master` (  `corporate_id` int( 11  )  unsigned NOT  NULL,
 `ecs_corp_id` int( 11  )  unsigned NOT  NULL ,
 `name` varchar( 100  )  NOT  NULL ,
 `address` varchar( 255  )  NOT  NULL ,
 `city` varchar( 100  )  NOT  NULL ,
 `state` varchar( 100  )  NOT  NULL ,
 `pincode` varchar( 10  )  NOT  NULL ,
 `contact_number` varchar( 100  )  NOT  NULL ,
 `contact_email` varchar( 100  )  NOT  NULL ,
 `by_ops_id` int( 11  )  unsigned NOT  NULL ,
 `ip` varchar( 16  )  NOT  NULL ,
 `date_created` timestamp NULL  DEFAULT NULL ,
 `date_updated` timestamp NOT  NULL  DEFAULT CURRENT_TIMESTAMP  ON  UPDATE  CURRENT_TIMESTAMP ,
 `status` enum(  'active',  'inactive'  )  NOT  NULL DEFAULT  'active' ) ENGINE  = InnoDB  DEFAULT CHARSET  = latin1;

SET @flag_id := (SELECT id FROM `t_flags` WHERE name='agent-hic_ratnakar_hospital');
SET @product_id := (SELECT id FROM `t_products` WHERE name='MEDI ASSIST CARD');

INSERT INTO `t_privileges` (`name`, `flag_id`, `description`) VALUES ('edit', @flag_id, 'Edit Hospital');
SET @privilege_id = last_insert_id();
INSERT INTO `t_product_privileges` (`product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (@product_id, @flag_id, @privilege_id, 1);

SET @product_id := (SELECT id FROM `t_products` WHERE name='MEDI ASSIST CARD');

INSERT INTO `t_product_privileges` (`product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (@product_id, '28', '146', 1),
 (@product_id, '33', '173', 1), (@product_id, '35', '193', 1),
 (@product_id, '35', '194', 1), (@product_id, '33', '203', 1),
 (@product_id, '32', '208', 1), (@product_id, '32', '209', 1),
 (@product_id, '32', '210', 1), (@product_id, '33', '224', 1),
 (@product_id, '33', '225', 1), (@product_id, '33', '228', 1),
 (@product_id, '33', '239', 1), (@product_id, '33', '240', 1),
 (@product_id, '33', '253', 1), (@product_id, '33', '254', 1);
 
 INSERT INTO `t_flags` (`id`, `name`, `description`, `active_on_dev`, `active_on_prod`) VALUES
(NULL, 'agent-hic_ratnakar_cardload', 'Load Card', 1, 0);
SET @flag_id = last_insert_id();

SET @product_id_val = (SELECT id FROM t_products WHERE name = 'MEDI ASSIST CARD');
INSERT INTO `t_privileges` (`name`, `flag_id`, `description`) VALUES ('search', @flag_id, 'Search HIC Cardholder');
SET @privilege_index_id = last_insert_id();
INSERT INTO `t_privileges` (`name`, `flag_id`, `description`) VALUES ('load', @flag_id, 'Load HIC Cardholder');
SET @privilege_search_id = last_insert_id();

INSERT INTO `t_product_privileges` (`product_id`, `flag_id`, `privilege_id`, `allow`) VALUES
(@product_id_val, @flag_id, @privilege_index_id, 1),
(@product_id_val, @flag_id, @privilege_search_id, 1);

CREATE TABLE IF NOT EXISTS `rat_hic_insurance_claim` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int(11) unsigned NOT NULL,
  `customer_id` int(11) unsigned NOT NULL,
  `cardholder_id` int(11) unsigned NOT NULL,
  `amount` decimal(11,2) NOT NULL,
  `hospital_id_code` int(11) unsigned NOT NULL,
  `txn_type` char(4) NOT NULL,
  `txn_code` int(11) unsigned NOT NULL,
  `by_agent_id` int(11) unsigned NOT NULL,
  `ip` varchar(16) NOT NULL,
  `date_created` timestamp NULL DEFAULT NULL,
  `date_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `num_fail_loads` tinyint(4) NOT NULL DEFAULT '0',
  `status` enum('pending','loaded','failed','cutoff') NOT NULL DEFAULT 'pending',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;


----------------------------------------------------------------------

ALTER TABLE hic_hospital RENAME TO rat_hic_hospital;
ALTER TABLE hic_terminal RENAME TO rat_hic_terminal;
ALTER TABLE log_hic_hospital RENAME TO log_rat_hic_hospital;
ALTER TABLE log_hic_terminal RENAME TO log_rat_hic_terminal;
ALTER TABLE hic_cardholders RENAME TO rat_hic_cardholders;
ALTER TABLE hic_cardholder_details RENAME TO rat_hic_cardholder_details;


INSERT INTO `t_flags` (`name`, `description`, `active_on_dev`, `active_on_prod`) VALUES ('agent-hic_ratnakar_cardholder', 'Cardholder of ratnakar bank hic', '1', '0');
SET @flag_id = last_insert_id();
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'add', @flag_id, 'Add Cardholder');
SET @privilege_id = last_insert_id();
SET @product_id := (SELECT id from t_products where name='MEDI ASSIST CARD');
INSERT INTO `t_product_privileges` (`id`, `product_id`, `flag_id`, privilege_id, `allow`) VALUES (NULL, @product_id, @flag_id, @privilege_id, 1);

------------------------------------------------------------------------------------------

update t_flags set name='agent-corp_ratnakar_cardholder' where name='agent-hic_ratnakar_cardholder' limit 1;
update t_flags set name='agent-corp_ratnakar_hospital' where name='agent-hic_ratnakar_hospital' limit 1;
update t_flags set name='operation-corp_ratnakar_cardholder' where name='operation-hic_ratnakar_cardholder' limit 1;
update t_flags set name='agent-corp_ratnakar_cardload' where name='agent-hic_ratnakar_cardload' limit 1;
ALTER TABLE  `rat_hic_cardholders` ADD  `aadhaar_no` VARCHAR( 20 ) NULL DEFAULT NULL AFTER  `last_name` ,
ADD  `pan` VARCHAR( 10 ) NULL DEFAULT NULL AFTER  `aadhaar_no`;

-------------------------------------------------------------------------------------------

SET @flag_id := (SELECT id FROM `t_flags` WHERE name='agent-corp_ratnakar_cardholder');
INSERT INTO `t_privileges` (`name`, `flag_id`, `description`) VALUES ('search', @flag_id, 'Search Corporate Cardholder');
SET @privilege_id = last_insert_id(); 
SET @product_id := (SELECT id FROM `t_products` WHERE name='MEDI ASSIST CARD');
INSERT INTO `t_product_privileges` (`product_id`, `flag_id`, `privilege_id`, `allow`) VALUES
(@product_id, @flag_id, @privilege_id, 1);
INSERT INTO `t_privileges` (`name`, `flag_id`, `description`) VALUES ('active', @flag_id, 'Activate Corporate Cardholder');
SET @privilege_id = last_insert_id(); 
INSERT INTO `t_product_privileges` (`product_id`, `flag_id`, `privilege_id`, `allow`) VALUES
(@product_id, @flag_id, @privilege_id, 1);

-- FIXED removed DB name from table name
UPDATE `t_products` SET `program_type` = 'Corp' WHERE `t_products`.`name` ='MEDI ASSIST CARD';
RENAME TABLE `rat_hic_cardholders` TO `rat_corp_cardholders` ;
RENAME TABLE `rat_hic_cardholder_details` TO `rat_corp_cardholder_details` ;
RENAME TABLE `rat_hic_hospital` TO `rat_corp_hospital` ;
RENAME TABLE `rat_hic_insurance_claim` TO `rat_corp_insurance_claim` ;
RENAME TABLE `rat_hic_terminal` TO `rat_corp_terminal` ;
RENAME TABLE `hic_insurance_claim` TO `corp_insurance_claim` ;
RENAME TABLE `log_rat_hic_hospital` TO `log_rat_corp_hospital` ;
RENAME TABLE `log_rat_hic_terminal` TO `log_rat_corp_terminal` ;


SET @flag_id := (SELECT id FROM `t_flags` WHERE name='agent-corp_ratnakar_cardholder');
INSERT INTO `t_privileges` (`name`, `flag_id`, `description`) VALUES ('inactive', @flag_id, 'Deactivate Corporate Cardholder');
SET @privilege_id = last_insert_id(); 
SET @product_id := (SELECT id FROM `t_products` WHERE name='MEDI ASSIST CARD');
INSERT INTO `t_product_privileges` (`product_id`, `flag_id`, `privilege_id`, `allow`) VALUES
(@product_id, @flag_id, @privilege_id, 1);




CREATE TABLE `purse_master` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `bank_id` int(11) unsigned NOT NULL,
  `product_id` int(11) unsigned NOT NULL,
  `code` char(6) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` varchar(255) NOT NULL,
  `initial_balance` decimal(11,2) NOT NULL,
  `max_balance` decimal(11,2) DEFAULT NULL,
  `date_created` timestamp NULL DEFAULT NULL,
  `date_updated` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1 COMMENT='This is product-purse configuration';


SET @bank_id_val := (SELECT id FROM `t_bank` WHERE unicode = '300' AND status = 'active');

SET @product_id_val := (SELECT id FROM `t_products` WHERE bank_id = @bank_id_val AND unicode = '310' AND status = 'active');

INSERT INTO `purse_master` (`bank_id`, `product_id`, `code`, `name`, `description`, `initial_balance`, `max_balance`, `date_created`, `status`) 
VALUES (@bank_id_val, @product_id_val, 'RCI310', 'Purse INS', 'Corporate Purse for Insurance', '0.0', '10000000.00', NOW(), 'active');

INSERT INTO `purse_master` (`bank_id`, `product_id`, `code`, `name`, `description`, `initial_balance`, `max_balance`, `date_created`, `status`)  
VALUES (@bank_id_val, @product_id_val, 'RCH310', 'Purse HR', 'Corporate Purse for HR', '0.0', '10000000.00', NOW(), 'active');

SET @product_id := (select id from t_products where name='MEDI ASSIST CARD');
SET @flag_id := (SELECT id FROM t_flags WHERE name = 'agent-corp_ratnakar_cardload');
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'checkstatus', @flag_id, 'Check Card Load Status');
SET @privilege_id = last_insert_id();
Insert into `t_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @product_id, @flag_id, @privilege_id, '1');
ALTER TABLE `rat_corp_cardholders` CHANGE `status` `status` ENUM( 'pending', 'active', 'inactive', 'incomplete', 'failed', 'pending_reg' ) NOT NULL DEFAULT 'pending';
ALTER TABLE `rat_corp_cardholders` ADD `failed_reason` VARCHAR( 200 ) NOT NULL AFTER `date_updated` ,
ADD `date_failed` TIMESTAMP NOT NULL AFTER `failed_reason`;


ALTER TABLE `rat_corp_cardholders`
DROP COLUMN `upload_status`,
MODIFY COLUMN `status`  enum('active','inactive','ecs_pending','ecs_failed') CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT 'ecs_pending' ,
ADD COLUMN `date_failed`  timestamp NULL AFTER `date_updated`,
ADD COLUMN `failed_reason`  varchar(200) NULL AFTER `status`;


CREATE TABLE `rat_corp_cardholders_batch` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `card_number` varchar(10) NOT NULL,
  `afn` varchar(10) NOT NULL,
  `medi_assist_id` varchar(10) NOT NULL,
  `employee_id` varchar(10) NOT NULL,
  `first_name` varchar(26) NOT NULL,
  `middle_name` varchar(26) NOT NULL,
  `last_name` varchar(26) NOT NULL,
  `aadhaar_no` varchar(20) DEFAULT NULL,
  `pan` varchar(10) DEFAULT NULL,
  `gender` enum('male','female') NOT NULL,
  `date_of_birth` date NOT NULL,
  `mobile` varchar(15) NOT NULL,
  `email` varchar(100) NOT NULL,
  `employer_name` varchar(100) NOT NULL,
  `corporate_id` varchar(11) NOT NULL,
  `batch_name` varchar(100) NOT NULL,
  `by_ops_id` int(11) unsigned NOT NULL,
  `date_created` timestamp NULL DEFAULT NULL,
  `date_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `upload_status` enum('temp','incomplete','pass','duplicate') NOT NULL DEFAULT 'temp',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

---------------------------------------------------------------------------------

DROP TABLE IF EXISTS `rat_corp_cardholders_batch`;
DROP TABLE IF EXISTS `rat_corp_cardholders`;
CREATE TABLE IF NOT EXISTS `rat_corp_cardholders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) unsigned NOT NULL,
  `customer_master_id` int(11) unsigned NOT NULL,
  `crn` bigint(20) unsigned NOT NULL,
  `unicode` varchar(16) NOT NULL,
  `card_number` varchar(16) NOT NULL,
  `afn` varchar(10) NOT NULL,
  `medi_assist_id` varchar(10) NOT NULL,
  `employee_id` varchar(10) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `middle_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) NOT NULL,
  `name_on_card` varchar(100) NOT NULL,
  `gender` enum('male','female') NOT NULL,
  `date_of_birth` varchar(10) NOT NULL,
  `mobile` varchar(15) NOT NULL,
  `email` varchar(50) DEFAULT NULL,
  `landline` varchar(15) DEFAULT NULL,
  `address_line1` varchar(50) NOT NULL,
  `address_line2` varchar(50) DEFAULT NULL,
  `city` varchar(50) NOT NULL,
  `pincode` int(10) NOT NULL,
  `mother_maiden_name` varchar(25) NOT NULL,
  `employer_name` varchar(50) NOT NULL,
  `corporate_id` varchar(16) NOT NULL,
  `corp_address_line1` varchar(50) DEFAULT NULL,
  `corp_address_line2` varchar(50) NOT NULL,
  `corp_city` varchar(50) NOT NULL,
  `corp_pin` int(10) NOT NULL,
  `id_proof_type` varchar(30) NOT NULL,
  `id_proof_number` varchar(50) NOT NULL,
  `address_proof_type` varchar(30) NOT NULL,
  `address_proof_number` varchar(50) NOT NULL,
  `by_ops_id` int(11) NOT NULL,
  `batch_name` varchar(100) NOT NULL,
  `date_created` datetime NOT NULL,
  `date_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `failed_reason` varchar(200) NOT NULL,
  `date_failed` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `status` enum('active','inactive','ecs_pending','ecs_failed') NOT NULL DEFAULT 'ecs_pending',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `rat_corp_cardholder_batch`
--

DROP TABLE IF EXISTS `rat_corp_cardholder_batch`;
CREATE TABLE IF NOT EXISTS `rat_corp_cardholder_batch` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) unsigned NOT NULL,
  `afn` varchar(10) NOT NULL,
  `medi_assist_id` varchar(10) NOT NULL,
  `employee_id` varchar(10) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `middle_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) NOT NULL,
  `name_on_card` varchar(100) NOT NULL,
  `gender` enum('male','female') NOT NULL,
  `date_of_birth` varchar(10) NOT NULL,
  `mobile` varchar(15) NOT NULL,
  `email` varchar(50) DEFAULT NULL,
  `landline` varchar(15) DEFAULT NULL,
  `address_line1` varchar(50) NOT NULL,
  `address_line2` varchar(50) DEFAULT NULL,
  `city` varchar(50) NOT NULL,
  `pincode` int(10) NOT NULL,
  `mother_maiden_name` varchar(25) NOT NULL,
  `employer_name` varchar(50) NOT NULL,
  `corporate_id` varchar(16) NOT NULL,
  `corp_address_line1` varchar(50) DEFAULT NULL,
  `corp_address_line2` varchar(50) NOT NULL,
  `corp_city` varchar(50) NOT NULL,
  `corp_pin` int(10) NOT NULL,
  `id_proof_type` varchar(30) NOT NULL,
  `id_proof_number` varchar(50) NOT NULL,
  `address_proof_type` varchar(30) NOT NULL,
  `address_proof_number` varchar(50) NOT NULL,
  `by_ops_id` int(11) NOT NULL,
  `batch_name` varchar(100) NOT NULL,
  `date_created` datetime NOT NULL,
  `date_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `upload_status` enum('temp','incomplete','pass','duplicate') NOT NULL DEFAULT 'temp',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

CREATE TABLE `rat_txn_customer` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `txn_code` int(11) unsigned NOT NULL,
  `customer_master_id` int(11) unsigned NOT NULL,
  `txn_customer_master_id` int(11) unsigned DEFAULT NULL,
  `txn_agent_id` int(11) unsigned DEFAULT NULL,
  `txn_ops_id` int(11) unsigned DEFAULT NULL,
  `product_id` int(11) unsigned DEFAULT NULL,
  `purse_master_id` int(11) unsigned NOT NULL,
  `customer_purse_id` int(11) unsigned NOT NULL,
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



ALTER TABLE `rat_corp_insurance_claim`
ADD COLUMN `customer_purse_id`  int(11) UNSIGNED NOT NULL AFTER `amount`;

DROP TABLE IF EXISTS `rat_customer_master`;
CREATE TABLE IF NOT EXISTS `rat_customer_master` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `customer_master_id` int(11) unsigned NOT NULL,
  `shmart_crn` int(11) unsigned DEFAULT NULL,
  `first_name` varchar(50) DEFAULT NULL,
  `middle_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) DEFAULT NULL,
  `aadhaar_no` varchar(20) DEFAULT NULL,
  `pan` varchar(10) DEFAULT NULL,
  `mobile_country_code` varchar(6) DEFAULT NULL,
  `mobile` varchar(15) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `gender` enum('male','female') DEFAULT 'male',
  `date_of_birth` date DEFAULT NULL,
  `status` enum('incomplete','pending','active','inactive') DEFAULT 'pending',
  PRIMARY KEY (`id`),
  UNIQUE KEY `unq_shmart_crn` (`shmart_crn`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `rat_customer_product` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `rat_customer_id` int(11) unsigned DEFAULT NULL,
  `product_id` int(11) unsigned DEFAULT NULL,
  `program_type` varchar(10) DEFAULT NULL,
  `bank_id` int(11) unsigned DEFAULT NULL,
  `by_agent_id` int(11) unsigned DEFAULT NULL,
  `by_ops_id` int(11) unsigned DEFAULT NULL,
  `date_created` timestamp NULL DEFAULT NULL,
  `date_updated` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `status` enum('active','inactive') DEFAULT 'active',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;


-----------------------------------------------------------------------------------

ALTER TABLE `rat_corp_cardholder_batch` CHANGE `gender` `gender` VARCHAR( 10 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL;
ALTER TABLE `rat_corp_cardholder_batch` CHANGE `upload_status` `upload_status` ENUM( 'temp', 'incomplete', 'pass', 'duplicate', 'rejected' ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT 'temp';

-- @flag_id = agent-agentfunding, @priv_id = index // for agent funding
SET @product_id := (SELECT id FROM t_products WHERE unicode = '310' AND status = 'active' LIMIT 1);
Insert into `t_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @product_id, @flag_id, @privilege_id, '1');


SET @flag_id := (SELECT id FROM `t_flags` WHERE name='agent-agentfunding');
-- @priv_id = 'viewfundrequest', @flag_id, 'Agent should be able to view his fund request details.');
SET @product_id := (SELECT id FROM t_products WHERE unicode = '310' AND status = 'active' LIMIT 1);
Insert into `t_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @product_id, @flag_id, @privilege_id, '1');


SET @flag_id := (SELECT id FROM `t_flags` WHERE name='agent-agentfunding');
-- @priv_id = 'requestfund', @flag_id, 'Agent Funding-agent can request for fund'
SET @product_id := (SELECT id FROM t_products WHERE unicode = '310' AND status = 'active' LIMIT 1);
Insert into `t_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @product_id, @flag_id, @privilege_id, '1');