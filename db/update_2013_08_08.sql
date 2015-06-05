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


ALTER TABLE `t_txn_agent`
ADD COLUMN `txn_customer_master_id`  int(11) UNSIGNED NOT NULL AFTER `agent_id`,
ADD COLUMN `purse_master_id`  int(11) UNSIGNED NOT NULL AFTER `remittance_request_id`,
ADD COLUMN `customer_purse_id`  int(11) UNSIGNED NOT NULL AFTER `purse_master_id`;

