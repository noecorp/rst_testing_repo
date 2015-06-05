/*
Code not pushed on production
*/

CREATE TABLE IF NOT EXISTS `hic_cardholders` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `unicode` bigint(20) unsigned NOT NULL,
  `crn` varchar(16) NOT NULL,
  `card_number` varchar(10) NOT NULL,
  `afn` varchar(10) NOT NULL,
  `medi_assist_id` varchar(10) NOT NULL,
  `emp_id` varchar(10) NOT NULL,
  `first_name` varchar(26) NOT NULL,
  `middle_name` varchar(26) NOT NULL,
  `last_name` varchar(26) NOT NULL,
  `gender` enum('male','female') NOT NULL,
  `date_of_birth` date NOT NULL,
  `mobile` varchar(15) NOT NULL,
  `email` int(100) DEFAULT NULL,
  `employer_name` int(100) NOT NULL,
  `corporate_id` int(11) unsigned NOT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;


CREATE TABLE IF NOT EXISTS `hic_cardholder_details` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `cardholder_id` int(11) unsigned NOT NULL,
  `unicode` bigint(20) unsigned NOT NULL,
  `crn` varchar(16) NOT NULL,
  `card_number` varchar(10) NOT NULL,
  `afn` varchar(10) NOT NULL,
  `medi_assist_id` varchar(10) NOT NULL,
  `emp_id` varchar(10) NOT NULL,
  `first_name` varchar(26) NOT NULL,
  `middle_name` varchar(26) NOT NULL,
  `last_name` varchar(26) NOT NULL,
  `gender` enum('male','female') NOT NULL,
  `date_of_birth` date NOT NULL,
  `mobile` varchar(15) NOT NULL,
  `email` int(100) DEFAULT NULL,
  `employer_name` int(100) NOT NULL,
  `corporate_id` int(11) unsigned NOT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;


INSERT INTO `t_flags` (`id`, `name`, `description`, `active_on_dev`, `active_on_prod`) VALUES (NULL, 'operation-hic_ratnakar', 'Health Insurance Claim - Ratnakar Bank Module', '1', '0');
SET @flag_id = last_insert_id();
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'index', @flag_id, 'Index Listing for HIC Ratnakar Bank'), (NULL, 'uploadcardholder', @flag_id, 'Bulk upload of cardholders for HIC Ratnakar Bank');

UPDATE `t_flags` SET `name` = 'operation-hic_ratnakar_cardholder' WHERE `name` = 'operation-hic_ratnakar';

INSERT INTO `t_unicode_conf` (`unicode_initials`, `bank_unicode`, `product_unicode`) VALUES ('30031000', '300', '310');

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