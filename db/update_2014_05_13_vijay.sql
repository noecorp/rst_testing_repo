--
-- Table structure for table `corporate_balance`
--

CREATE TABLE IF NOT EXISTS `corporate_balance` (
  `corporate_id` int(11) NOT NULL,
  `amount` decimal(20,2) NOT NULL DEFAULT '0.00',
  `block_amount` decimal(20,2) NOT NULL DEFAULT '0.00',
  `date_modified` datetime DEFAULT NULL,
  PRIMARY KEY (`corporate_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `corporate_bind_limit`
--

CREATE TABLE IF NOT EXISTS `corporate_bind_limit` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `corporate_id` int(11) unsigned NOT NULL,
  `corporate_limit_id` int(11) unsigned NOT NULL,
  `date_start` date NOT NULL,
  `date_end` date NOT NULL,
  `by_ops_id` int(11) unsigned NOT NULL,
  `by_corporate_id` int(11) unsigned DEFAULT NULL,
  `date_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1  ;

-- --------------------------------------------------------

--
-- Table structure for table `corporate_bind_product_commission`
--

CREATE TABLE IF NOT EXISTS `corporate_bind_product_commission` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `corporate_id` int(11) unsigned NOT NULL,
  `product_id` int(11) unsigned NOT NULL,
  `product_limit_id` int(11) unsigned NOT NULL,
  `plan_commission_id` int(11) unsigned NOT NULL,
  `plan_fee_id` int(11) unsigned DEFAULT NULL,
  `by_ops_id` int(11) unsigned NOT NULL,
  `by_corporate_id` int(11) unsigned DEFAULT NULL,
  `date_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_start` date NOT NULL,
  `date_end` date NOT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1  ;

-- --------------------------------------------------------

--
-- Table structure for table `corporate_funding`
--

CREATE TABLE IF NOT EXISTS `corporate_funding` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `corporate_id` int(11) unsigned NOT NULL,
  `amount` decimal(11,2) NOT NULL DEFAULT '0.00',
  `mode` enum('cr','dr') NOT NULL DEFAULT 'cr',
  `txn_type` char(4) NOT NULL DEFAULT 'AGFL',
  `fund_transfer_type_id` int(11) unsigned NOT NULL,
  `funding_no` varchar(50) NOT NULL,
  `funding_details` varchar(255) DEFAULT NULL,
  `comments` varchar(255) NOT NULL,
  `ip_agent` varchar(15) NOT NULL,
  `date_request` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `bank_statement_id` int(11) unsigned NOT NULL,
  `settlement_by` enum('system','ops') DEFAULT NULL,
  `settlement_by_ops_id` int(11) unsigned DEFAULT NULL,
  `settlement_ip_ops` varchar(15) DEFAULT NULL,
  `settlement_date` timestamp NULL DEFAULT NULL,
  `settlement_remarks` varchar(255) DEFAULT NULL,
  `status` enum('approved','pending','rejected','duplicate') NOT NULL DEFAULT 'pending',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1  ;

-- --------------------------------------------------------

--
-- Table structure for table `corporate_fund_transfer`
--

CREATE TABLE IF NOT EXISTS `corporate_fund_transfer` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `txn_code` int(11) unsigned NOT NULL,
  `corporate_id` int(11) NOT NULL,
  `txn_corporate_id` int(11) NOT NULL,
  `amount` decimal(11,2) NOT NULL,
  `txn_type` char(4) NOT NULL,
  `status` enum('pending','success','failure') NOT NULL DEFAULT 'pending',
  `date_created` datetime NOT NULL,
  `date_updated` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1  ;

-- --------------------------------------------------------

--
-- Table structure for table `corporate_limit`
--

CREATE TABLE IF NOT EXISTS `corporate_limit` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `currency` char(3) NOT NULL,
  `cnt_out_max_txn_daily` int(11) unsigned NOT NULL,
  `cnt_out_max_txn_monthly` int(11) unsigned NOT NULL,
  `cnt_out_max_txn_yearly` int(11) unsigned NOT NULL,
  `limit_out_max_daily` int(11) unsigned NOT NULL,
  `limit_out_max_monthly` int(11) unsigned NOT NULL,
  `limit_out_max_yearly` int(11) unsigned NOT NULL,
  `limit_out_min_txn` int(11) unsigned NOT NULL,
  `limit_out_max_txn` int(11) unsigned NOT NULL,
  `by_ops_id` int(11) unsigned NOT NULL,
  `date_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status` enum('active','inactive','deleted') NOT NULL DEFAULT 'active',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1  ;

-- --------------------------------------------------------

--
-- Table structure for table `corporate_master`
--

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
) ENGINE=InnoDB DEFAULT CHARSET=latin1  ;

-- --------------------------------------------------------

--
-- Table structure for table `corporate_object_relations`
--

CREATE TABLE IF NOT EXISTS `corporate_object_relations` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `from_object_id` int(11) unsigned NOT NULL DEFAULT '0',
  `to_object_id` int(11) unsigned NOT NULL DEFAULT '0',
  `object_relation_type_id` int(11) unsigned NOT NULL DEFAULT '0',
  `date_start` date DEFAULT '0000-00-00',
  `date_end` date DEFAULT '0000-00-00',
  `status` enum('active','inactive') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'active',
  `comment` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `object_relations_ibfk_1` (`object_relation_type_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci  ;

-- --------------------------------------------------------

--
-- Table structure for table `corporate_txn`
--

CREATE TABLE IF NOT EXISTS `corporate_txn` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `txn_code` int(11) unsigned NOT NULL,
  `corporate_id` int(11) unsigned NOT NULL,
  `txn_customer_master_id` int(11) unsigned DEFAULT NULL,
  `txn_cardholder_id` int(11) unsigned DEFAULT NULL,
  `txn_corporate_id` int(11) unsigned DEFAULT NULL,
  `txn_ops_id` int(11) unsigned DEFAULT NULL,
  `txn_remitter_id` int(11) unsigned DEFAULT NULL,
  `kotak_remitter_id` int(11) unsigned DEFAULT NULL,
  `product_id` int(11) unsigned DEFAULT NULL,
  `corporate_fund_request_id` int(11) unsigned DEFAULT NULL,
  `corporate_funding_id` int(11) unsigned DEFAULT NULL,
  `remittance_request_id` int(11) unsigned DEFAULT NULL,
  `kotak_remittance_request_id` int(11) unsigned DEFAULT NULL,
  `insurance_claim_id` int(11) unsigned DEFAULT NULL,
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
  `date_updated` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `txn_code` (`txn_code`,`mode`,`txn_type`,`corporate_id`) USING BTREE
) ENGINE=InnoDB  DEFAULT CHARSET=latin1  ;

-- --------------------------------------------------------

--
-- Table structure for table `corporate_users`
--

CREATE TABLE IF NOT EXISTS `corporate_users` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `corporate_code` int(11) unsigned NOT NULL,
  `first_name` varchar(20) NOT NULL,
  `last_name` varchar(40) NOT NULL,
  `password` varchar(128) NOT NULL,
  `email` varchar(340) NOT NULL,
  `mobile` varchar(20) NOT NULL,
  `last_login` timestamp NULL DEFAULT NULL,
  `last_password_update` timestamp NULL DEFAULT '0000-00-00 00:00:00',
  `auth_code` varchar(20) NOT NULL,
  `num_login_attempts` tinyint(4) NOT NULL DEFAULT '0',
  `session_id` varchar(30) DEFAULT NULL,
  `by_ops_id` int(11) unsigned DEFAULT NULL,
  `date_created` datetime NOT NULL,
  `date_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `is_logged` tinyint(1) NOT NULL DEFAULT '0',
  `user_type` tinyint(4) DEFAULT '0',
  `enroll_status` enum('approved','pending','rejected','incomplete') CHARACTER SET latin1 NOT NULL,
  `email_verification_id` int(11) NOT NULL,
  `email_verification_status` enum('verified','pending') NOT NULL,
  `status` enum('blocked','unblocked','locked') NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_email` (`email`(255)) USING BTREE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

-- --------------------------------------------------------

--
-- Table structure for table `corporate_users_details`
--

CREATE TABLE IF NOT EXISTS `corporate_users_details` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `corporate_user_id` int(11) NOT NULL,
  `email` varchar(50) NOT NULL,
  `auth_email` varchar(100) NOT NULL,
  `title` enum('Mr','Mrs','Ms','Dr','Prof') NOT NULL,
  `first_name` varchar(35) NOT NULL,
  `middle_name` varchar(35) NOT NULL,
  `last_name` varchar(35) NOT NULL,
  `ip` varchar(15) NOT NULL,
  `mobile1` varchar(15) NOT NULL,
  `mobile2` varchar(15) NOT NULL,
  `afn` varchar(30) NOT NULL,
  `profile_photo` varchar(100) DEFAULT NULL,
  `father_first_name` varchar(20) CHARACTER SET utf8 NOT NULL,
  `father_middle_name` varchar(20) CHARACTER SET utf8 NOT NULL,
  `father_last_name` varchar(20) CHARACTER SET utf8 NOT NULL,
  `spouse_first_name` varchar(20) CHARACTER SET utf8 NOT NULL,
  `spouse_middle_name` varchar(20) CHARACTER SET utf8 NOT NULL,
  `spouse_last_name` varchar(20) CHARACTER SET utf8 NOT NULL,
  `mother_maiden_name` varchar(20) CHARACTER SET utf8 NOT NULL,
  `estab_name` varchar(80) CHARACTER SET utf8 NOT NULL,
  `home` varchar(100) CHARACTER SET utf8 NOT NULL,
  `office` varchar(80) CHARACTER SET utf8 NOT NULL,
  `shop` varchar(80) CHARACTER SET utf8 NOT NULL,
  `education_level` varchar(20) NOT NULL,
  `matric_school_name` varchar(30) CHARACTER SET utf8 NOT NULL,
  `intermediate_school_name` varchar(30) CHARACTER SET utf8 NOT NULL,
  `graduation_degree` varchar(40) CHARACTER SET utf8 NOT NULL,
  `graduation_college` varchar(40) CHARACTER SET utf8 NOT NULL,
  `p_graduation_degree` varchar(40) CHARACTER SET utf8 NOT NULL,
  `p_graduation_college` varchar(40) CHARACTER SET utf8 NOT NULL,
  `other_degree` varchar(40) CHARACTER SET utf8 NOT NULL,
  `other_college` varchar(40) CHARACTER SET utf8 NOT NULL,
  `date_of_birth` date NOT NULL,
  `fund_account_type` varchar(40) CHARACTER SET utf8 NOT NULL,
  `gender` enum('male','female','institution') NOT NULL,
  `Identification_type` varchar(50) NOT NULL,
  `Identification_number` varchar(30) CHARACTER SET utf8 NOT NULL,
  `passport_expiry` date NOT NULL,
  `address_proof_type` varchar(50) NOT NULL,
  `address_proof_number` varchar(20) NOT NULL,
  `pan_number` varchar(10) CHARACTER SET utf8 NOT NULL,
  `flat_no` varchar(12) CHARACTER SET utf8 NOT NULL,
  `estab_address1` varchar(50) CHARACTER SET utf8 NOT NULL,
  `estab_address2` varchar(50) CHARACTER SET utf8 NOT NULL,
  `estab_city` varchar(50) CHARACTER SET utf8 NOT NULL,
  `estab_taluka` varchar(50) CHARACTER SET utf8 NOT NULL,
  `estab_district` varchar(50) CHARACTER SET utf8 NOT NULL,
  `estab_state` varchar(50) CHARACTER SET utf8 NOT NULL,
  `estab_country` varchar(50) CHARACTER SET utf8 NOT NULL,
  `estab_pincode` int(10) NOT NULL,
  `res_type` varchar(15) CHARACTER SET utf8 NOT NULL,
  `res_address1` varchar(50) CHARACTER SET utf8 NOT NULL,
  `res_address2` varchar(50) CHARACTER SET utf8 NOT NULL,
  `res_city` varchar(50) CHARACTER SET utf8 NOT NULL,
  `res_taluka` varchar(50) CHARACTER SET utf8 NOT NULL,
  `res_district` varchar(50) CHARACTER SET utf8 NOT NULL,
  `res_state` varchar(50) CHARACTER SET utf8 NOT NULL,
  `res_country` varchar(50) CHARACTER SET utf8 NOT NULL,
  `res_pincode` int(10) NOT NULL,
  `bank_name` varchar(50) NOT NULL,
  `bank_account_number` varchar(35) NOT NULL,
  `team_manager_approval` varchar(12) CHARACTER SET utf8 NOT NULL,
  `bank_id` int(30) NOT NULL,
  `bank_location` varchar(100) CHARACTER SET utf8 NOT NULL,
  `bank_city` varchar(30) CHARACTER SET utf8 NOT NULL,
  `bank_ifsc_code` varchar(30) CHARACTER SET utf8 NOT NULL,
  `branch_id` varchar(15) NOT NULL,
  `bank_area` varchar(100) CHARACTER SET utf8 NOT NULL,
  `bank_branch_id` int(11) NOT NULL,
  `operation_head_approval` varchar(12) CHARACTER SET utf8 NOT NULL,
  `bank_approval` varchar(12) CHARACTER SET utf8 NOT NULL,
  `amount_bal` int(11) NOT NULL,
  `closure_request` varchar(512) CHARACTER SET utf8 NOT NULL,
  `closure_date` datetime NOT NULL,
  `occupation` varchar(30) CHARACTER SET utf8 NOT NULL,
  `id_proof1` varchar(30) CHARACTER SET utf8 NOT NULL,
  `id_proof2` varchar(30) CHARACTER SET utf8 NOT NULL,
  `address_proof` varchar(30) CHARACTER SET utf8 NOT NULL,
  `annual_income` int(15) NOT NULL,
  `computer_literacy` varchar(30) CHARACTER SET utf8 NOT NULL,
  `political_linkage` varchar(10) CHARACTER SET utf8 NOT NULL,
  `declaration` varchar(10) CHARACTER SET utf8 NOT NULL,
  `place` varchar(30) CHARACTER SET utf8 NOT NULL,
  `date_created` datetime NOT NULL,
  `by_ops_id` int(11) unsigned NOT NULL,
  `auth_email_verification_id` int(11) unsigned NOT NULL,
  `auth_email_verification_status` enum('pending','verified') NOT NULL DEFAULT 'pending',
  `status` enum('active','inactive') NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;



INSERT INTO `t_flags` (`id`, `name`, `description`, `active_on_dev`, `active_on_prod`) VALUES (NULL, 'operation-approvecorporate', 'Manage Pending Corporates', 1, 0);
SET @flag_id :=  last_insert_id();

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES 
(NULL, 'reject', @flag_id, 'Reject Corporate'),
(NULL, 'approve', @flag_id, 'Approve Corporate'),
(NULL, 'index', @flag_id, 'Approval Pending Corporate Listing'),
(NULL, 'rejectedlist', @flag_id, 'Rejected Corporates Listing');


INSERT INTO `t_flags` (`id` ,`name` ,`description` ,`active_on_dev` ,`active_on_prod`) VALUES (NULL, 'corporate-corporatefunding', 'Corporate Funding', '0', '0');
SET @flag_id :=  last_insert_id();
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES(NULL, 'fundrequest', @flag_id, 'Corporate should be able to view his fund requests. The requests should also display the status and remarks entered by operation if approved/rejected.');
SET @privileges_id :=  last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 3, @flag_id, @privileges_id, 1);
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 2, @flag_id, @privileges_id, 1);
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 4, @flag_id, @privileges_id, 1);

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES(NULL, 'viewfundrequest', @flag_id, 'Corporate should be able to view his fund request details.');
SET @privileges_id :=  last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 3, @flag_id, @privileges_id, 1);
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 2, @flag_id, @privileges_id, 1);
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 4, @flag_id, @privileges_id, 1);

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES(NULL, 'requestfund', @flag_id, 'Corporate Funding-agent can request for fund');
SET @privileges_id :=  last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 3, @flag_id, @privileges_id, 1);
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 2, @flag_id, @privileges_id, 1);
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 4, @flag_id, @privileges_id, 1);

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES(NULL, 'index', @flag_id, 'Corporate Funding');
SET @privileges_id :=  last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 3, @flag_id, @privileges_id, 1);
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 2, @flag_id, @privileges_id, 1);
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 4, @flag_id, @privileges_id, 1);


INSERT INTO `t_flags` (`id`, `name`, `description`, `active_on_dev`, `active_on_prod`) VALUES (NULL, 'operation-corporatefunding', 'Corporate Funding', '0', '0');
SET @flag_id :=  last_insert_id();

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'uploadbankstatement', @flag_id, 'Upload Bank Statement');
SET @privileges_id :=  last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, 4, @flag_id, @privileges_id, 1);

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'exportpendingfundrequest', @flag_id, 'Export pending fund request');
SET @privileges_id :=  last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, 4, @flag_id, @privileges_id, 1);


INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'exportunsettledbankstatement', @flag_id, 'Export unsettled bank statement');
SET @privileges_id :=  last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, 4, @flag_id, @privileges_id, 1);

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'exportsettledfundrequest', @flag_id, 'Export settled fund request');
SET @privileges_id :=  last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, 4, @flag_id, @privileges_id, 1);

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'index', @flag_id, 'Operation Corporate Funding Index');
SET @privileges_id :=  last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, 4, @flag_id, @privileges_id, 1);

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'afteruploadbankstatement', @flag_id, 'Operation go to here internally after upload bank statement');
SET @privileges_id :=  last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, 4, @flag_id, @privileges_id, 1);

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'pendingfundrequest', @flag_id, 'Operation user can see pending fund requests');
SET @privileges_id :=  last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, 4, @flag_id, @privileges_id, 1);

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'confirmbeforesettlement', @flag_id, 'Operation user can select bank statement for settlement');
SET @privileges_id :=  last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, 4, @flag_id, @privileges_id, 1);

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'confirmsettlement', @flag_id, 'Operation user can see details of pending fund & bank statement');
SET @privileges_id :=  last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, 4, @flag_id, @privileges_id, 1);

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'dosettlement', @flag_id, 'Operation user process bank statement for settlement with fund request');
SET @privileges_id :=  last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, 4, @flag_id, @privileges_id, 1);

-- #2622

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'disbursementload', '80', 'NSDC File Disbursement Load');
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, '3', '80', '533', '1');
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'confirmbeforerejectfundrequest', @flag_id, 'Operation user confirms before rejecting fundrequest');
SET @privileges_id :=  last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, 4, @flag_id, @privileges_id, 1);

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'rejectfundrequest', @flag_id, 'Operation user reject fundrequest');
SET @privileges_id :=  last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, 4, @flag_id, @privileges_id, 1);

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'unsettledbankstatement', @flag_id, 'List of unsettled banks statements');
SET @privileges_id :=  last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, 4, @flag_id, @privileges_id, 1);

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'settledfundrequest', @flag_id, 'Operation user see settled fund request');
SET @privileges_id :=  last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, 4
, @flag_id, @privileges_id, 1);

INSERT INTO `t_flags` (`id`, `name`, `description`, `active_on_dev`, `active_on_prod`) VALUES (NULL, 'corporate-corp_ratnakar_cardholder', 'Cardholder of ratnakar by corporate', '1', '0');
SET @flag_id :=  last_insert_id();
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'add', @flag_id, 'Add Cardholder');
SET @privileg_id :=  last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 4, @flag_id, @privileg_id, 1);
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'uploadcardholders', @flag_id, 'Add Cardholder');
SET @privileg_id :=  last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 4, @flag_id, @privileg_id, 1);

INSERT INTO `t_flags` (`id`, `name`, `description`, `active_on_dev`, `active_on_prod`) VALUES (NULL, 'corporate-signup', 'Corporate Signup', '1', '0');
SET @flag_id :=  last_insert_id();
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES(NULL, 'index', @flag_id, 'Corporate Signup - enter Mobile number');
SET @privileg_id :=  last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 4, @flag_id, @privileg_id, 1);
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES(NULL, 'verification', @flag_id, 'Corporate signup mobile verification code');
SET @privileg_id :=  last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 4, @flag_id, @privileg_id, 1);
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES(NULL, 'add', @flag_id, 'Corporate signup, add basic details');
SET @privileg_id :=  last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 4, @flag_id, @privileg_id, 1);
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES(NULL, 'addeducation', @flag_id, 'Corporate signup, add education details');
SET @privileg_id :=  last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 4, @flag_id, @privileg_id, 1);
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES(NULL, 'addidentification', @flag_id, 'Corporate signup, add identification details');
SET @privileg_id :=  last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 4, @flag_id, @privileg_id, 1);
SET @privileg_id :=  last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 4, @flag_id, @privileg_id, 1);
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES(NULL, 'addaddress', @flag_id, 'Corporate signup, add address details');
SET @privileg_id :=  last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 4, @flag_id, @privileg_id, 1);
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES(NULL, 'addbank', @flag_id, 'Corporate signup, add bank details');
SET @privileg_id :=  last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 4, @flag_id, @privileg_id, 1);
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES(NULL, 'detailscomplete', @flag_id, 'Corporate signup, details complete');
SET @privileg_id :=  last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 4, @flag_id, @privileg_id, 1);

INSERT INTO `t_flags` (`id`, `name`, `description`, `active_on_dev`, `active_on_prod`) VALUES (NULL, 'corporate-corp_ratnakar_cardload', 'Card load of corp customers', '1', '0');
SET @flag_id :=  last_insert_id();
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'corporateload', @flag_id, 'Card load');
SET @privileg_id :=  last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 4, @flag_id, @privileg_id, 1);

INSERT INTO `t_flags` (`id`, `name`, `description`, `active_on_dev`, `active_on_prod`) VALUES (NULL, 'corporate-linkedcorporates', 'Linked corporates', '1', '0');
SET @flag_id :=  last_insert_id();
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'supercorporate', @flag_id, 'Display index');
SET @privileg_id :=  last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 4, @flag_id, @privileg_id, 1);
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'subcorporatelisting', @flag_id, 'Display registered users list');
SET @privileg_id :=  last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 4, @flag_id, @privileg_id, 1);
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'fundtrfr', @flag_id, 'Display registered users list');
SET @privileg_id :=  last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 4, @flag_id, @privileg_id, 1);
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 2, @flag_id, @privileg_id, 1);
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'retrievefund', @flag_id, 'Display registered users list');
SET @privileg_id :=  last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 4, @flag_id, @privileg_id, 1);
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 2, @flag_id, @privileg_id, 1);
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'fundtrfrconfirm', @flag_id, 'Display registered users list');
SET @privileg_id :=  last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 4, @flag_id, @privileg_id, 1);
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 2, @flag_id, @privileg_id, 1);
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'retrievetrfrconfirm', @flag_id, 'Display registered users list');
SET @privileg_id :=  last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 4, @flag_id, @privileg_id, 1);
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 2, @flag_id, @privileg_id, 1);



INSERT INTO `t_flags` (`id`, `name`, `description`, `active_on_dev`, `active_on_prod`) VALUES (NULL, 'corporate-corp_ratnakar_reports', 'Corporate ratnaker reports', '1', '0');
SET @flag_id :=  last_insert_id();
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'loadreport', @flag_id, 'Card load report');
SET @privileg_id :=  last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 4, @flag_id, @privileg_id, 1);
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'activecards', @flag_id, 'Active cards report');
SET @privileg_id :=  last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 4, @flag_id, @privileg_id, 1);



INSERT INTO `t_transaction_type` (`typecode`, `name`, `status`, `date_created`, `is_comm`) VALUES ('CGFL', 'Corporate Fund Load', 'active', '2014-05-15 05:44:11', 'no');

INSERT INTO `t_transaction_type` (`typecode`, `name`, `status`, `date_created`, `is_comm`) VALUES ('CCFT', 'Corporate to corporate fund transfer', 'active', CURRENT_TIMESTAMP, 'no'), ('RCFT', 'Corporate to corporate fund reversal', 'active', CURRENT_TIMESTAMP, 'no');







ALTER TABLE  `t_txn_ops` ADD  `txn_corporate_id` INT NOT NULL AFTER  `txn_agent_id` ;
ALTER TABLE `t_txn_ops`  ADD `corporate_fund_request_id` INT(11) NULL DEFAULT NULL AFTER `agent_funding_id`,  ADD `corporate_funding_id` INT(11) NULL DEFAULT NULL AFTER `corporate_fund_request_id`;
ALTER TABLE `rat_corp_cardholder_batch` ADD `by_corporate_id` INT( 11 ) NULL DEFAULT NULL AFTER `by_ops_id` ;
ALTER TABLE `rat_corp_cardholders` ADD `by_corporate_id` INT( 11 ) NULL DEFAULT NULL AFTER `by_ops_id`;
ALTER TABLE `rat_customer_product` ADD `by_corporate_id` INT( 11 ) NULL DEFAULT NULL AFTER `by_agent_id` ;
ALTER TABLE `rat_corp_load_request_batch` ADD `by_corporate_id` INT( 11 ) NULL DEFAULT NULL AFTER `by_ops_id` ;
ALTER TABLE `t_log_change_password` ADD `corporate_id` INT( 11 ) NOT NULL AFTER `corporate_id` ;
ALTER TABLE  `t_log_forgot_password` ADD  `corporate_id` INT( 11 ) NULL DEFAULT NULL AFTER `agent_id`;


INSERT INTO `t_flags` (`id`, `name`, `description`, `active_on_dev`, `active_on_prod`) VALUES (NULL, 'operation-approvedcorporate', 'Manage Corporates', '1', '0');
SET @flag_id :=  last_insert_id();

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES(null, 'index', @flag_id, 'Corporate Products and Commission Listing');
SET @privileg_id :=  last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 9, @flag_id, @privileg_id, 1);
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 3, @flag_id, @privileg_id, 1);
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 8, @flag_id, @privileg_id, 1);

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES(null, 'corporateproduct', @flag_id, 'Assign Corporate Products and Commission');
SET @privileg_id :=  last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 9, @flag_id, @privileg_id, 1);
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 3, @flag_id, @privileg_id, 1);


INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES(null, 'editcorporateproduct', @flag_id, 'Edit Assign Corporate Products and Commission');
SET @privileg_id :=  last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 9, @flag_id, @privileg_id, 1);
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 3, @flag_id, @privileg_id, 1);

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES(null, 'deletecorporateproduct', @flag_id, 'Delete Assign Corporate Products and Commission');
SET @privileg_id :=  last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 9, @flag_id, @privileg_id, 1);
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 3, @flag_id, @privileg_id, 1);


INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES(null, 'assigncorporatelimits', @flag_id, 'After Approving Corporate - Assign Limits');
SET @privileg_id :=  last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 9, @flag_id, @privileg_id, 1);
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 3, @flag_id, @privileg_id, 1);



INSERT INTO `t_corporate_groups` (`id`, `name`, `parent_id`) VALUES
(2, 'regional', 0),
(3, 'local', 0),
(4, 'head', 0);

-- --------------------------------------------------------

--
-- Table structure for table `t_corporate_users_groups`
--

CREATE TABLE IF NOT EXISTS `t_corporate_users_groups` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `group_id` int(11) unsigned NOT NULL,
  `user_id` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_bank_group` (`group_id`,`user_id`) USING BTREE,
  KEY `idx_user_id` (`user_id`) USING BTREE,
  KEY `idx_group_id` (`group_id`) USING BTREE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=7 ;

--
-- Dumping data for table `t_corporate_users_groups`
--

INSERT INTO `t_flags` (`id`, `name`, `description`, `active_on_dev`, `active_on_prod`) VALUES
(NULL, 'corporate-profile', 'Corporate Portal', 1, 0);
SET @flag_id :=  last_insert_id();
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES(NULL, 'authcode', @flag_id, '');
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES(NULL, 'changepassword', @flag_id, '');
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES(NULL, 'forgotpassword', @flag_id, '');
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES(NULL, 'index', @flag_id, '');
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES(NULL, 'login', @flag_id, '');
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES(NULL, 'logout', @flag_id, '');
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES(NULL, 'newpassword', @flag_id, '');
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES(NULL, 'resendauthcode', @flag_id, '');


INSERT INTO `t_flags` (`id`, `name`, `description`, `active_on_dev`, `active_on_prod`) VALUES
(NULL, 'corporate-ajax', 'Corporate ajax', 1, 0);



SET @flag_id := (SELECT id FROM `t_flags` where name ='corporate-corporatefunding');
SET @privileges_id :=  (SELECT id FROM `t_privileges` where name ='fundrequest' and flag_id=@flag_id);
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 3, @flag_id, @privileges_id, 1);
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 2, @flag_id, @privileges_id, 1);
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 4, @flag_id, @privileges_id, 1);


SET @privileges_id :=  (SELECT id FROM `t_privileges` where name ='viewfundrequest' and flag_id=@flag_id);
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 3, @flag_id, @privileges_id, 1);
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 2, @flag_id, @privileges_id, 1);
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 4, @flag_id, @privileges_id, 1);


SET @privileges_id :=  (SELECT id FROM `t_privileges` where name ='requestfund' and flag_id=@flag_id);
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 3, @flag_id, @privileges_id, 1);
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 2, @flag_id, @privileges_id, 1);
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 4, @flag_id, @privileges_id, 1);



SET @privileges_id :=  (SELECT id FROM `t_privileges` where name ='index' and flag_id=@flag_id);
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 3, @flag_id, @privileges_id, 1);
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 2, @flag_id, @privileges_id, 1);
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 4, @flag_id, @privileges_id, 1);


SET @flag_id :=  select id from `t_flags` where `name` = 'corporate-linkedcorporates';
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'fundtrfr', @flag_id, 'Display registered users list');
SET @privileg_id :=  last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 4, @flag_id, @privileg_id, 1);
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 2, @flag_id, @privileg_id, 1);
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'retrievefund', @flag_id, 'Display registered users list');
SET @privileg_id :=  last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 4, @flag_id, @privileg_id, 1);
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 2, @flag_id, @privileg_id, 1);
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'fundtrfrconfirm', @flag_id, 'Display registered users list');
SET @privileg_id :=  last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 4, @flag_id, @privileg_id, 1);
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 2, @flag_id, @privileg_id, 1);
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'retrievetrfrconfirm', @flag_id, 'Display registered users list');
SET @privileg_id :=  last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 4, @flag_id, @privileg_id, 1);
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 2, @flag_id, @privileg_id, 1);




--04/06/2014 04:39pm--

INSERT INTO `t_flags` (`id`, `name`, `description`, `active_on_dev`, `active_on_prod`) VALUES(NULL, 'operation-corporates', 'Manage Corporates', 1, 0);
SET @flag_id :=  last_insert_id();

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES(NULL, 'index', @flag_id, 'Corporates Listing');
SET @privileg_id :=  last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 9, @flag_id, @privileg_id, 1);
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 8, @flag_id, @privileg_id, 1);
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 3, @flag_id, @privileg_id, 1);

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES(NULL, 'add', @flag_id, 'Add Corporates');
SET @privileg_id :=  last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 9, @flag_id, @privileg_id, 1);
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 8, @flag_id, @privileg_id, 1);
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 3, @flag_id, @privileg_id, 1);

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES(NULL, 'edit', @flag_id, 'Edit Corporates');
SET @privileg_id :=  last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 9, @flag_id, @privileg_id, 1);
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 8, @flag_id, @privileg_id, 1);
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 3, @flag_id, @privileg_id, 1);

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES(NULL, 'delete', @flag_id, 'Delete Corporates');
SET @privileg_id :=  last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 9, @flag_id, @privileg_id, 1);
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 8, @flag_id, @privileg_id, 1);
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 3, @flag_id, @privileg_id, 1);



INSERT INTO `t_flags` (`id`, `name`, `description`, `active_on_dev`, `active_on_prod`) VALUES(NULL, 'operation-corporatefunding', 'Corporate Funding', 0, 0);
SET @flag_id :=  last_insert_id();

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES(NULL, 'uploadbankstatement', @flag_id, 'Upload Bank Statement');
SET @privileg_id :=  last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 9, @flag_id, @privileg_id, 1);
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 8, @flag_id, @privileg_id, 1);
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 3, @flag_id, @privileg_id, 1);

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES(NULL, 'exportpendingfundrequest', @flag_id, 'Export pending fund request');
SET @privileg_id :=  last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 9, @flag_id, @privileg_id, 1);
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 8, @flag_id, @privileg_id, 1);
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 3, @flag_id, @privileg_id, 1);

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES(NULL, 'exportunsettledbankstatement', @privileg_id, 'Export unsettled bank statement');
SET @privileg_id :=  last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 9, @flag_id, @privileg_id, 1);
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 8, @flag_id, @privileg_id, 1);
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 3, @flag_id, @privileg_id, 1);

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES(NULL, 'exportsettledfundrequest', @flag_id, 'Export settled fund request');
SET @privileg_id :=  last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 9, @flag_id, @privileg_id, 1);
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 8, @flag_id, @privileg_id, 1);
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 3, @flag_id, @privileg_id, 1);

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES(NULL, 'index', @flag_id, 'Operation Corporate Funding Index');
SET @privileg_id :=  last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 9, @flag_id, @privileg_id, 1);
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 8, @flag_id, @privileg_id, 1);
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 3, @flag_id, @privileg_id, 1);

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES(NULL, 'afteruploadbankstatement', @flag_id, 'Operation go to here internally after upload bank statement');
SET @privileg_id :=  last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 9, @flag_id, @privileg_id, 1);
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 8, @flag_id, @privileg_id, 1);
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 3, @flag_id, @privileg_id, 1);

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES(NULL, 'pendingfundrequest', @flag_id, 'Operation user can see pending fund requests');
SET @privileg_id :=  last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 9, @flag_id, @privileg_id, 1);
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 8, @flag_id, @privileg_id, 1);
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 3, @flag_id, @privileg_id, 1);

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES(NULL, 'confirmbeforesettlement', @flag_id, 'Operation user can select bank statement for settlement');
SET @privileg_id :=  last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 9, @flag_id, @privileg_id, 1);
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 8, @flag_id, @privileg_id, 1);
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 3, @flag_id, @privileg_id, 1);

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES(NULL, 'confirmsettlement', @flag_id, 'Operation user can see details of pending fund & bank statement');
SET @privileg_id :=  last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 9, @flag_id, @privileg_id, 1);
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 8, @flag_id, @privileg_id, 1);
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 3, @flag_id, @privileg_id, 1);

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES(NULL, 'dosettlement', @flag_id, 'Operation user process bank statement for settlement with fund request');
SET @privileg_id :=  last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 9, @flag_id, @privileg_id, 1);
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 8, @flag_id, @privileg_id, 1);
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 3, @flag_id, @privileg_id, 1);

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES(NULL, 'confirmbeforerejectfundrequest', @flag_id, 'Operation user confirms before rejecting fundrequest');
SET @privileg_id :=  last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 9, @flag_id, @privileg_id, 1);
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 8, @flag_id, @privileg_id, 1);
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 3, @flag_id, @privileg_id, 1);

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES(NULL, 'rejectfundrequest', @flag_id, 'Operation user reject fundrequest');
SET @privileg_id :=  last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 9, @flag_id, @privileg_id, 1);
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 8, @flag_id, @privileg_id, 1);
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 3, @flag_id, @privileg_id, 1);

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES(NULL, 'unsettledbankstatement', @flag_id, 'List of unsettled banks statements');
SET @privileg_id :=  last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 9, @flag_id, @privileg_id, 1);
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 8, @flag_id, @privileg_id, 1);
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 3, @flag_id, @privileg_id, 1);

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES(NULL, 'settledfundrequest', @flag_id, 'Operation user see settled fund request');
SET @privileg_id :=  last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 9, @flag_id, @privileg_id, 1);
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 8, @flag_id, @privileg_id, 1);
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 3, @flag_id, @privileg_id, 1);




SET @flag_id :=  select id from `t_flags` where `name` = 'operation-approvecorporate';
SET @privileg_id :=  select id from `t_privileges` where `name` = 'reject' and `flag_id`=@flag_id;
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 9, @flag_id, @privileg_id, 1);
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 8, @flag_id, @privileg_id, 1);
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 3, @flag_id, @privileg_id, 1);

SET @privileg_id :=  select id from `t_privileges` where `name` = 'approve' and `flag_id`=@flag_id;
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 9, @flag_id, @privileg_id, 1);
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 8, @flag_id, @privileg_id, 1);
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 3, @flag_id, @privileg_id, 1);

SET @privileg_id :=  select id from `t_privileges` where `name` = 'index' and `flag_id`=@flag_id;
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 9, @flag_id, @privileg_id, 1);
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 8, @flag_id, @privileg_id, 1);
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 3, @flag_id, @privileg_id, 1);

SET @privileg_id :=  select id from `t_privileges` where `name` = 'rejectedlist' and `flag_id`=@flag_id;
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 9, @flag_id, @privileg_id, 1);
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 8, @flag_id, @privileg_id, 1);
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 3, @flag_id, @privileg_id, 1);



--05/06/2014--

INSERT INTO `t_flags` (`id`, `name`, `description`, `active_on_dev`, `active_on_prod`) VALUES (NULL, 'corporate-emailauthorization', 'Corporate Auth Email authorization', '1', '0');
SET @flag_id :=  last_insert_id();
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES(NULL, 'index', @flag_id, 'Corporate Auth Email authorization');
SET @privileg_id :=  last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 4, @flag_id, @privileg_id, 1);
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 3, @flag_id, @privileg_id, 1);
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 2, @flag_id, @privileg_id, 1);





SET @flag_id :=  select id from `t_flags` where `name` = 'corporate-signup';
SET @privileg_id :=  select id from `t_privileges` where `name` = 'index' and `flag_id`=@flag_id;
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 2, @flag_id, @privileg_id, 1);

SET @privileg_id :=  select id from `t_privileges` where `name` = 'verification' and `flag_id`=@flag_id;
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 2, @flag_id, @privileg_id, 1);

SET @privileg_id :=  select id from `t_privileges` where `name` = 'add' and `flag_id`=@flag_id;
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 2, @flag_id, @privileg_id, 1);

SET @privileg_id :=  select id from `t_privileges` where `name` = 'addeducation' and `flag_id`=@flag_id;
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 2, @flag_id, @privileg_id, 1);

SET @privileg_id :=  select id from `t_privileges` where `name` = 'addidentification' and `flag_id`=@flag_id;
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 2, @flag_id, @privileg_id, 1);

SET @privileg_id :=  select id from `t_privileges` where `name` = 'addaddress' and `flag_id`=@flag_id;
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 2, @flag_id, @privileg_id, 1);

SET @privileg_id :=  select id from `t_privileges` where `name` = 'addbank' and `flag_id`=@flag_id;
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 2, @flag_id, @privileg_id, 1);

SET @privileg_id :=  select id from `t_privileges` where `name` = 'detailscomplete' and `flag_id`=@flag_id;
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 2, @flag_id, @privileg_id, 1);









SET @flag_id :=  (select id from `t_flags` where `name` = 'corporate-signup');
SET @privileg_id :=  (select id from `t_privileges` where `name` = 'index' and `flag_id`=@flag_id);
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 2, @flag_id, @privileg_id, 1);

SET @privileg_id :=  (select id from `t_privileges` where `name` = 'verification' and `flag_id`=@flag_id);
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 2, @flag_id, @privileg_id, 1);

SET @privileg_id :=  (select id from `t_privileges` where `name` = 'add' and `flag_id`=@flag_id);
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 2, @flag_id, @privileg_id, 1);

SET @privileg_id :=  (select id from `t_privileges` where `name` = 'addeducation' and `flag_id`=@flag_id);
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 2, @flag_id, @privileg_id, 1);

SET @privileg_id :=  (select id from `t_privileges` where `name` = 'addidentification' and `flag_id`=@flag_id);
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 2, @flag_id, @privileg_id, 1);

SET @privileg_id :=  (select id from `t_privileges` where `name` = 'addaddress' and `flag_id`=@flag_id);
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 2, @flag_id, @privileg_id, 1);

SET @privileg_id :=  (select id from `t_privileges` where `name` = 'addbank' and `flag_id`=@flag_id);
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 2, @flag_id, @privileg_id, 1);

SET @privileg_id :=  (select id from `t_privileges` where `name` = 'detailscomplete' and `flag_id`=@flag_id);
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 2, @flag_id, @privileg_id, 1);


ALTER TABLE  `t_log_forgot_password` ADD  `corporate_id` INT( 11 ) NULL DEFAULT NULL AFTER `agent_id`;







