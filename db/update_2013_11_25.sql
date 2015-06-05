CREATE TABLE `kotak_corp_cardholders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) unsigned NOT NULL,
  `kotak_customer_id` int(11) unsigned NOT NULL,
  `customer_master_id` int(11) unsigned NOT NULL,
  `crn` bigint(20) unsigned NOT NULL,
  `unicode` varchar(16) NOT NULL,
  `card_number` varchar(16) NOT NULL,
  `card_pack_id` varchar(20) NOT NULL,
  `afn` varchar(10) NOT NULL,
  `card_kit_id` varchar(50) NOT NULL,
  `amul_id` varchar(10) NOT NULL,
  `employee_id` varchar(10) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `middle_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) NOT NULL,
  `name_on_card` varchar(100) NOT NULL,
  `gender` enum('male','female') NOT NULL DEFAULT 'male',
  `date_of_birth` varchar(10) NOT NULL,
  `aadhaar_no` varchar(20) NOT NULL,
  `telephone` varchar(15) NOT NULL,
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
  `comm_address_line1` varchar(50) DEFAULT NULL,
  `comm_address_line2` varchar(50) NOT NULL,
  `comm_city` varchar(50) NOT NULL,
  `comm_pin` int(10) NOT NULL,
  `id_proof_type` varchar(30) NOT NULL,
  `id_proof_number` varchar(50) NOT NULL,
  `id_proof_doc_id` int(11) unsigned NOT NULL,
  `address_proof_type` varchar(30) NOT NULL,
  `address_proof_number` varchar(50) NOT NULL,
  `address_proof_doc_id` int(11) unsigned NOT NULL,
  `photo_doc_id` int(11) unsigned NOT NULL,
  `other_id_proof` varchar(255) DEFAULT NULL,
  `by_ops_id` int(11) NOT NULL,
  `by_agent_id` int(11) unsigned NOT NULL,
  `batch_id` int(11) unsigned NOT NULL,
  `batch_name` varchar(100) NOT NULL,
  `society_id` varchar(10) NOT NULL,
  `society_name` varchar(50) NOT NULL,
  `nominee_name` varchar(100) NOT NULL,
  `nominee_relationship` varchar(20) NOT NULL,
  `date_created` datetime NOT NULL,
  `place_application` varchar(100) NOT NULL,
  `date_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `failed_reason` varchar(200) NOT NULL,
  `date_failed` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `status` enum('active','inactive','ecs_pending','ecs_failed') NOT NULL DEFAULT 'ecs_pending',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

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

CREATE TABLE `kotak_customer_purse` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `kotak_customer_id` int(11) unsigned DEFAULT NULL,
  `purse_master_id` int(11) unsigned NOT NULL,
  `bank_id` int(11) unsigned NOT NULL,
  `product_id` int(11) unsigned NOT NULL,
  `amount` decimal(11,2) NOT NULL,
  `block_amount` decimal(11,2) NOT NULL,
  `date_updated` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='amount is balance amount';

SET @product_id := (SELECT id FROM `t_products` WHERE `unicode` = 510 AND status ='active');
INSERT INTO `t_product_privileges` (`product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (@product_id, '28', '146', 1);
INSERT INTO `t_product_privileges` (`product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (@product_id, '33', '173', 1);
INSERT INTO `t_product_privileges` (`product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (@product_id, '35', '194', 1);
INSERT INTO `t_product_privileges` (`product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (@product_id, '32', '208', 1);
INSERT INTO `t_product_privileges` (`product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (@product_id, '32', '210', 1);
INSERT INTO `t_product_privileges` (`product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (@product_id, '35', '193', 1);
INSERT INTO `t_product_privileges` (`product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (@product_id, '33', '203', 1);
INSERT INTO `t_product_privileges` (`product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (@product_id, '32', '209', 1);
