CREATE TABLE IF NOT EXISTS `boi_delivery_file_master` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) unsigned NOT NULL,
  `nsdc_enrollment_no` varchar(16) NOT NULL,
  `sol_id` varchar(5) NOT NULL,
  `title` varchar(5) NOT NULL,
  `name` varchar(100) NOT NULL,
  `occupation` varchar(5) NOT NULL,
  `gender` enum('male','female') NOT NULL DEFAULT 'male',
  `date_of_birth` varchar(10) NOT NULL,
  `address_type` char(1) NOT NULL DEFAULT 'C',
  `address_line1` varchar(50) NOT NULL,
  `address_line2` varchar(50) DEFAULT NULL,
  `city` varchar(50) NOT NULL,
  `state` varchar(50) NOT NULL,
  `pincode` int(10) NOT NULL,
  `country_code` varchar(5) NOT NULL,
  `comm_address_line1` varchar(50) DEFAULT NULL,
  `comm_address_line2` varchar(50) NOT NULL,
  `comm_city` varchar(50) NOT NULL,
  `comm_pin` int(10) NOT NULL,
  `comm_state` varchar(50) NOT NULL,
  `comm_country_code` varchar(5) NOT NULL,
  `landline` varchar(15) DEFAULT NULL,
  `mobile` varchar(15) NOT NULL,
  `email` varchar(50) DEFAULT NULL,
  `pan` varchar(10) NOT NULL,
 `uid_no` varchar(16) NOT NULL,
 `nre_flag` char(1) NOT NULL DEFAULT 'N',
 `nre_nationality` varchar(5) NOT NULL,
 `passport` varchar(12) NOT NULL,
 `passport_issue_date` date NOT NULL,
  `passport_expiry_date` date NOT NULL,
  `marital_status` char(1) NOT NULL,
  `cust_comm_code` char(1) NOT NULL,
  `other_bank_account_no` varchar(20) NOT NULL,
  `other_bank_account_type` varchar(20) NOT NULL,
  `other_bank_name` varchar(50) NOT NULL,
  `other_bank_branch` varchar(50) NOT NULL,
  `employer_name` varchar(50) NOT NULL,
  `employer_address_line1` varchar(45) NOT NULL,
  `employer_address_line2` varchar(45) NOT NULL,
  `employer_address_city` varchar(20) NOT NULL,
  `employer_address_state` varchar(20) NOT NULL,
  `employer_address_country_code` varchar(5) NOT NULL,
  `employer_address_pincode` varchar(15) NOT NULL,
  `employer_contact_no` varchar(15) NOT NULL,
  `minor_flg` char(1) NOT NULL DEFAULT 'N',
  `minor_guardian_code` varchar(10) NOT NULL,
  `minor_guardian_name` varchar(80) NOT NULL,
  `minor_guardian_address_line1` varchar(45) NOT NULL,
  `minor_guardian_address_line2` varchar(45) NOT NULL,
  `minor_guardian_city` varchar(20) NOT NULL,
  `minor_guardian_state` varchar(20) NOT NULL,
  `minor_guardian_pincode` varchar(15) NOT NULL,
  `minor_guardian_country_code` varchar(5) NOT NULL,
   `mode_of_operation` varchar(3) NOT NULL DEFAULT '001',
  `nomination_flg` varchar(1) NOT NULL,
  `nominee_name` varchar(100) NOT NULL,
  `nominee_relationship` varchar(20) NOT NULL,
  `nominee_add_line1` varchar(50) NOT NULL,
  `nominee_add_line2` varchar(50) NOT NULL,
  `nominee_city_cd` varchar(5) NOT NULL,
  `nominee_minor_guradian_cd` varchar(5) NOT NULL,
  `nominee_dob` date NOT NULL,
  `nominee_minor_flag` char(1) NOT NULL,
  `amount_open` varchar(40) NOT NULL,
  `mode_of_payment_open` varchar(40) NOT NULL,
  `account_no` varchar(16) NOT NULL,
  `cust_id` varchar(9) NOT NULL,
  `sqlid` varchar(40) NOT NULL,
  `finacle_status` char(1) NOT NULL,
  `update_sql_status` char(1) NOT NULL,
   `staff_flg` char(1) NOT NULL DEFAULT 'N',
  `staff_no` varchar(20) NOT NULL,
  `minor_title_guradian_code` varchar(5) NOT NULL,
  `passport_details` varchar(25) NOT NULL,
  `introducer_title_code` varchar(5) NOT NULL,
  `introducer_code` varchar(40) NOT NULL,
  `introducer_name` varchar(80) NOT NULL,
  `existing_cust_flg` char(1) NOT NULL DEFAULT 'N',
  `account_currency_code` varchar(3) NOT NULL DEFAULT 'INR',
  `cust_id_ver_flg` char(1) NOT NULL,
  `account_id_ver_flg` char(1) NOT NULL,
  `schm_code` varchar(20) NOT NULL,
  `orgaization_type` varchar(5) NOT NULL,
  `introducer_flg` char(1) NOT NULL,
  `introducer_cust_id` varchar(9) NOT NULL,
  `cust_currency_code` varchar(3) NOT NULL DEFAULT 'INR',
  `account_type_id` varchar(10) NOT NULL,
  `ref_num` varchar(20) NOT NULL,
  `card_number` varchar(16) NOT NULL,
  `card_pack_id` varchar(20) NOT NULL,
  `debit_mandate_account` varchar(20) NOT NULL,
  `boi_account_number` varchar(20) NOT NULL,
  `boi_customer_id` varchar(20) NOT NULL,
  `member_id` varchar(10) NOT NULL,
  `date_created` datetime NOT NULL,
  `delivery_date` datetime NOT NULL,
  `delivery_status` enum('delivered','undelivered') NOT NULL DEFAULT 'undelivered',
  `batch_name` varchar(100) NOT NULL,
  `date_ecs` datetime DEFAULT NULL,
  `failed_reason` varchar(100) DEFAULT NULL,
  `status` enum('pending','success','failure') NOT NULL DEFAULT 'pending',
    PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT = 1 ;


SET @flg_id := (SELECT id FROM `t_flags` where name ='operation-corp_boi_customer');

Insert into `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'deliverystatus', @flg_id, 'BOI Response File Report');

SET @priv_id = last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, '3', @flg_id, @priv_id, '1');

ALTER TABLE `boi_corp_cardholders`
MODIFY COLUMN `status`  enum('active','inactive','pending','ecs_failed','blocked') NOT NULL DEFAULT 'pending' AFTER `date_approval`,
ADD COLUMN `date_blocked`  datetime NULL AFTER `date_approval`;

SET @flg_id := (SELECT id FROM `t_flags` where name ='operation-corp_boi_index');


Insert into `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'crnstatus', @flg_id, 'CRN Status Report');
SET @privilege_id = last_insert_id();

Insert into `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, '3', @flg_id, @privilege_id, '1');


SET @flg_id := (SELECT id FROM `t_flags` where name ='operation-corp_boi_customer');

Insert into `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'uploaddeliveryflag', @flg_id, 'BOI Upload File');

SET @priv_id = last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, '3', @flg_id, @priv_id, '1');



SET @flg_id := (SELECT id FROM `t_flags` where name ='operation-corp_boi_index');
SET @privilege_id := (SELECT id FROM `t_privileges` where name ='index' AND flag_id = @flg_id);

Insert into `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, '3', @flg_id, @privilege_id, '1');
