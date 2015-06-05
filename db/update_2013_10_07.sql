-- Shmart Ideacts Product Setup
INSERT INTO `t_products` VALUES (NULL, '4', 'Shmart Ideacts', 'Shmart Ideacts Service by Kotak Bank', 'INR', '10000027', 'Remit', '610', '101', '127000000001', '2013-10-01 13:03:09', 'active');
SET @product_id :=  last_insert_id();
INSERT INTO `t_log_products` VALUES (product_id, '4', 'Shmart Ideacts', 'Shmart Ideacts Service by Kotak Bank', 'INR', '10000027', 'Remit', '610', '101', '127000000001', '2013-10-01 13:03:09', 'active');
INSERT INTO `t_unicode_conf` (`unicode_initials`, `bank_unicode`, `product_unicode`) VALUES ('40061000', '400', '610');


-- Kotak Amul Product Setup
INSERT INTO `t_products` VALUES (NULL, '4', 'Kotak Amul', 'Kotak Amul Service by Kotak Bank', 'INR', '10000027', 'Corp', '510', '101', '127000000001', '2013-10-01 13:03:09', 'active');
SET @product_id :=  last_insert_id();
INSERT INTO `t_log_products` VALUES (product_id, '4', 'Kotak Amul', 'Kotak Amul Service by Kotak Bank', 'INR', '10000027', 'Corp', '510', '101', '127000000001', '2013-10-01 13:03:09', 'active');
INSERT INTO `t_unicode_conf` (`unicode_initials`, `bank_unicode`, `product_unicode`) VALUES ('40051000', '400', '510');

-- Kotak Amul User tables --Need to enhance table as not yet received detailed requirement documents
DROP TABLE IF EXISTS `kotak_customer_master`;
CREATE TABLE `kotak_customer_master` (
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
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `kotak_corp_cardholders`;
CREATE TABLE `kotak_corp_cardholders` (
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
  `gender` enum('M','F') NOT NULL,
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
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS `kotak_corp_cardholder_details`;
CREATE TABLE `kotak_corp_cardholder_details` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `customer_master_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `cardholder_id` int(11) unsigned NOT NULL,
  `unicode` bigint(20) unsigned NOT NULL,
  `crn` varchar(16) NOT NULL,
  `card_number` varchar(10) NOT NULL,
  `afn` varchar(10) NOT NULL,
  `medi_assist_id` varchar(10) NOT NULL,
  `employee_id` varchar(10) NOT NULL,
  `first_name` varchar(26) NOT NULL,
  `middle_name` varchar(26) NOT NULL,
  `last_name` varchar(26) NOT NULL,
  `gender` enum('male','female') NOT NULL,
  `date_of_birth` date NOT NULL,
  `mobile` varchar(15) NOT NULL,
  `email` varchar(100) NOT NULL,
  `employer_name` varchar(100) NOT NULL,
  `corporate_id` varchar(11) NOT NULL,
  `ip` bigint(20) unsigned NOT NULL,
  `by_ops_id` int(11) unsigned NOT NULL,
  `date_created` timestamp NULL DEFAULT NULL,
  `date_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `status` enum('active','inactive','incomplete') NOT NULL DEFAULT 'incomplete',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


