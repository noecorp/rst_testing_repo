SET @product_id := (SELECT id FROM `t_products` WHERE `unicode` = 710 AND status ='active');

INSERT INTO `t_flags` (`name`, `description`, `active_on_dev`, `active_on_prod`) VALUES ('agent-corp_boi_customer', 'BOI Customers', '1', '0');
SET @flag_id_val = last_insert_id();

Insert into `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'adddetails', @flag_id_val, 'Add Customer Details');
SET @privilege_id = last_insert_id();

Insert into `t_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @product_id, @flag_id_val, @privilege_id, '1');

Insert into `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'complete', @flag_id_val, 'Add Customer Details Complete');
SET @privilege_id = last_insert_id();

Insert into `t_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @product_id, @flag_id_val, @privilege_id, '1');

Insert into `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'opsrejected', @flag_id_val, 'Ops Rejected Customer');
SET @privilege_id = last_insert_id();

Insert into `t_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @product_id, @flag_id_val, @privilege_id, '1');

Insert into `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'index', @flag_id_val, 'BOI Customer Index');
SET @privilege_id = last_insert_id();

Insert into `t_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @product_id, @flag_id_val, @privilege_id, '1');

Insert into `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'view', @flag_id_val, 'BOI Customer View Detail page');
SET @privilege_id = last_insert_id();

Insert into `t_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @product_id, @flag_id_val, @privilege_id, '1');

CREATE TABLE IF NOT EXISTS `boi_corp_cardholders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) unsigned NOT NULL,
  `boi_customer_id` int(11) unsigned NOT NULL,
  `customer_master_id` int(11) unsigned NOT NULL,
  `crn` bigint(20) unsigned NOT NULL,
  `unicode` varchar(16) NOT NULL,
  `card_number` varchar(16) NOT NULL,
  `card_pack_id` varchar(20) NOT NULL,
  `afn` varchar(10) NOT NULL,
  `member_id` varchar(10) NOT NULL,
  `employee_id` varchar(10) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `middle_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) NOT NULL,
  `name_on_card` varchar(100) NOT NULL,
  `gender` enum('male','female') NOT NULL DEFAULT 'male',
  `date_of_birth` varchar(10) NOT NULL,
  `aadhaar_no` varchar(20) NOT NULL,
  `mobile` varchar(15) NOT NULL,
  `email` varchar(50) DEFAULT NULL,
  `landline` varchar(15) DEFAULT NULL,
  `address_line1` varchar(50) NOT NULL,
  `address_line2` varchar(50) DEFAULT NULL,
  `city` varchar(50) NOT NULL,
  `state` varchar(50) NOT NULL,
  `pincode` int(10) NOT NULL,
  `mother_maiden_name` varchar(25) NOT NULL,
  `employer_name` varchar(50) NOT NULL,
  `corporate_id` varchar(16) NOT NULL,
  `comm_address_line1` varchar(50) DEFAULT NULL,
  `comm_address_line2` varchar(50) NOT NULL,
  `comm_city` varchar(50) NOT NULL,
  `comm_pin` int(10) NOT NULL,
  `comm_state` varchar(50) NOT NULL,
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
  `delivery_file_id` int(11) unsigned DEFAULT NULL,
  `date_activation` datetime NOT NULL,
  `failed_reason` varchar(200) NOT NULL,
  `date_failed` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `date_crn_update` datetime DEFAULT NULL,
  `date_authorize` datetime DEFAULT NULL,
  `recd_doc` enum('yes','no') NOT NULL DEFAULT 'no',
  `date_recd_doc` date DEFAULT NULL,
  `recd_doc_id` int(11) unsigned NOT NULL,
  `date_approval` datetime DEFAULT NULL,
  `status` enum('active','inactive','pending','ecs_failed') NOT NULL DEFAULT 'pending',
  `status_bank` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `status_ops` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `status_ecs` enum('pending','failure','success','waiting') NOT NULL DEFAULT 'waiting',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `boi_corp_cardholders_details`
--

CREATE TABLE IF NOT EXISTS `boi_corp_cardholders_details` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_customer_id` int(11) unsigned NOT NULL,
  `product_id` int(11) unsigned NOT NULL,
  `boi_customer_id` int(11) unsigned NOT NULL,
  `customer_master_id` int(11) unsigned NOT NULL,
  `crn` bigint(20) unsigned NOT NULL,
  `unicode` varchar(16) NOT NULL,
  `card_number` varchar(16) NOT NULL,
  `card_pack_id` varchar(20) NOT NULL,
  `afn` varchar(10) NOT NULL,
  `member_id` varchar(10) NOT NULL,
  `employee_id` varchar(10) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `middle_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) NOT NULL,
  `name_on_card` varchar(100) NOT NULL,
  `gender` enum('male','female') NOT NULL DEFAULT 'male',
  `date_of_birth` varchar(10) NOT NULL,
  `aadhaar_no` varchar(20) NOT NULL,
  `mobile` varchar(15) NOT NULL,
  `email` varchar(50) DEFAULT NULL,
  `landline` varchar(15) DEFAULT NULL,
  `address_line1` varchar(50) NOT NULL,
  `address_line2` varchar(50) DEFAULT NULL,
  `city` varchar(50) NOT NULL,
  `state` varchar(50) NOT NULL,
  `pincode` int(10) NOT NULL,
  `mother_maiden_name` varchar(25) NOT NULL,
  `employer_name` varchar(50) NOT NULL,
  `corporate_id` varchar(16) NOT NULL,
  `comm_address_line1` varchar(50) DEFAULT NULL,
  `comm_address_line2` varchar(50) NOT NULL,
  `comm_city` varchar(50) NOT NULL,
  `comm_pin` int(10) NOT NULL,
  `comm_state` varchar(50) NOT NULL,
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
  `delivery_file_id` int(11) unsigned DEFAULT NULL,
  `date_activation` datetime NOT NULL,
  `failed_reason` varchar(200) NOT NULL,
  `date_failed` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `status` enum('active','inactive','pending','ecs_failed') NOT NULL DEFAULT 'pending',
  `status_bank` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `status_ops` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `status_ecs` enum('pending','failure','success','waiting') NOT NULL DEFAULT 'waiting',
  `status_detail` enum('active','inactive') NOT NULL DEFAULT 'inactive',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `boi_corp_load_request`
--

CREATE TABLE IF NOT EXISTS `boi_corp_load_request` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int(11) unsigned NOT NULL,
  `customer_master_id` int(11) unsigned NOT NULL,
  `cardholder_id` int(11) unsigned NOT NULL,
  `purse_master_id` int(11) unsigned NOT NULL,
  `customer_purse_id` int(11) unsigned NOT NULL,
  `txn_type` char(4) NOT NULL,
  `load_channel` enum('medi-assist','ops') NOT NULL,
  `txn_identifier_type` char(3) NOT NULL,
  `card_number` varchar(30) NOT NULL,
  `member_id` varchar(10) NOT NULL,
  `amount` decimal(11,2) NOT NULL,
  `currency` char(3) NOT NULL,
  `narration` varchar(50) NOT NULL,
  `wallet_code` varchar(10) NOT NULL DEFAULT '',
  `txn_no` varchar(30) NOT NULL,
  `card_type` char(1) NOT NULL,
  `corporate_id` varchar(50) NOT NULL,
  `mode` enum('dr','cr') NOT NULL,
  `txn_code` int(11) unsigned NOT NULL,
  `by_agent_id` int(11) unsigned NOT NULL,
  `by_ops_id` int(11) unsigned NOT NULL,
  `ip` varchar(16) NOT NULL,
  `batch_name` varchar(100) NOT NULL,
  `date_created` datetime DEFAULT NULL,
  `date_load` datetime NOT NULL,
  `date_failed` datetime NOT NULL,
  `date_cutoff` datetime DEFAULT NULL,
  `txn_load_id` int(11) unsigned NOT NULL,
  `failed_reason` varchar(200) NOT NULL,
  `date_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `status` enum('pending','loaded','failed','cutoff','blocked','completed','incomplete') NOT NULL DEFAULT 'pending',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `boi_corp_load_request_batch`
--

CREATE TABLE IF NOT EXISTS `boi_corp_load_request_batch` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int(11) unsigned NOT NULL,
  `txn_identifier_type` char(3) NOT NULL,
  `card_number` varchar(30) NOT NULL,
  `member_id` varchar(10) NOT NULL,
  `amount` varchar(10) NOT NULL,
  `currency` char(3) NOT NULL,
  `narration` varchar(50) NOT NULL,
  `wallet_code` varchar(10) NOT NULL DEFAULT '',
  `txn_no` varchar(30) NOT NULL,
  `card_type` char(1) NOT NULL,
  `mode` enum('dr','cr') NOT NULL,
  `corporate_id` varchar(16) NOT NULL,
  `by_ops_id` int(11) unsigned NOT NULL,
  `ip` varchar(15) NOT NULL,
  `batch_name` varchar(100) NOT NULL,
  `date_created` datetime DEFAULT NULL,
  `date_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `failed_reason` varchar(200) NOT NULL,
  `upload_status` enum('temp','incomplete','pass','duplicate','rejected','failed') NOT NULL DEFAULT 'temp',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `boi_corp_log_cardholder`
--

CREATE TABLE IF NOT EXISTS `boi_corp_log_cardholder` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `boi_customer_id` int(11) unsigned NOT NULL,
  `product_customer_id` int(11) unsigned NOT NULL,
  `status_old` varchar(15) DEFAULT NULL,
  `status_new` varchar(15) DEFAULT NULL,
  `status_ops_old` varchar(15) DEFAULT NULL,
  `status_ops_new` varchar(15) DEFAULT NULL,
  `status_bank_old` varchar(15) DEFAULT NULL,
  `status_bank_new` varchar(15) DEFAULT NULL,
  `status_ecs_old` varchar(15) DEFAULT NULL,
  `status_ecs_new` varchar(15) DEFAULT NULL,
  `comments` tinytext,
  `by_type` enum('maker','checker','authorizer','ecs','system') NOT NULL,
  `by_id` int(11) unsigned NOT NULL,
  `ip` varchar(15) DEFAULT NULL,
  `date_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `boi_customer_purse`
--

CREATE TABLE IF NOT EXISTS `boi_customer_purse` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `boi_customer_id` int(11) unsigned DEFAULT NULL,
  `purse_master_id` int(11) unsigned NOT NULL,
  `bank_id` int(11) unsigned NOT NULL,
  `product_id` int(11) unsigned NOT NULL,
  `amount` decimal(11,2) NOT NULL,
  `block_amount` decimal(11,2) NOT NULL,
  `date_updated` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='amount is balance amount' AUTO_INCREMENT=1 ;


 CREATE  TABLE  `shmart`.`boi_txn_customer` (  `id` int( 11  )  unsigned NOT  NULL  AUTO_INCREMENT ,
 `txn_code` int( 11  )  unsigned NOT  NULL ,
 `customer_master_id` int( 11  )  unsigned NOT  NULL ,
 `txn_customer_master_id` int( 11  )  unsigned NOT  NULL ,
 `txn_agent_id` int( 11  )  unsigned NOT  NULL ,
 `txn_ops_id` int( 11  )  unsigned NOT  NULL ,
 `product_id` int( 11  )  unsigned NOT  NULL ,
 `insurance_claim_id` int( 11  )  unsigned NOT  NULL ,
 `purse_master_id` int( 11  )  unsigned NOT  NULL ,
 `customer_purse_id` int( 11  )  unsigned NOT  NULL ,
 `ip` varchar( 15  )  NOT  NULL ,
 `currency` char( 3  )  NOT  NULL ,
 `amount` decimal( 11, 2  )  NOT  NULL DEFAULT  '0.00',
 `mode` enum(  'cr',  'dr'  )  NOT  NULL ,
 `txn_type` char( 4  )  NOT  NULL ,
 `txn_status` enum(  'pending',  'success',  'failure'  )  NOT  NULL DEFAULT  'pending',
 `remarks` varchar( 255  )  NOT  NULL ,
 `date_created` timestamp NOT  NULL  DEFAULT CURRENT_TIMESTAMP ,
 `date_updated` timestamp NULL  DEFAULT NULL ,
 PRIMARY  KEY (  `id`  ) ,
 UNIQUE  KEY  `txn_code` (  `txn_code` ,  `mode` ,  `txn_type`  )  USING BTREE ) ENGINE  = InnoDB  DEFAULT CHARSET  = latin1;

ALTER TABLE `t_docs` ADD `doc_boi_cust_id` INT( 11 ) UNSIGNED NOT NULL AFTER `doc_kotak_amul_id`;