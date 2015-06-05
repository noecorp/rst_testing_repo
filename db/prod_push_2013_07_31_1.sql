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

SET @flag_id := (SELECT id FROM `t_flags` where name ='agent-remit_boi_beneficiary');
INSERT INTO `t_privileges` (`name`, `flag_id`, `description`) VALUES ('deactivatebeneficiary',@flag_id , 'Deactivate Beneficiary');
SET @product_id := (SELECT id from t_products WHERE program_type = 'Remit'); 
SET @priv_id := (SELECT id FROM t_privileges WHERE name = 'deactivatebeneficiary');
INSERT INTO `t_product_privileges` (`product_id`, `flag_id`, `privilege_id`, `allow`) VALUES
(@product_id, @flag_id, @priv_id, 1);

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


SET @flag_id := (SELECT id FROM `t_flags` WHERE name='operation-agents');
INSERT INTO `t_privileges` (`name`, `flag_id`, `description`) VALUES ('addauthemail', @flag_id, 'Add Auth email');

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
