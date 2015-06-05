INSERT INTO `t_cron` (`id`, `name`, `description`, `file_name`, `status`) VALUES ('31', 'BOI Disbursement File Generator', 'BOI Disbursement File Generator', 'BOIDisbFileGen.php', 'active');


SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-corp_boi_index'); 

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES
(NULL, 'disbursementload', @flag_id, 'NSDC File Disbursement Load'),
(NULL, 'disbursementreport', @flag_id, 'BOI NSDC Disbursement Load Report'),
(NULL, 'exportdisbursementreport', @flag_id, 'Export BOI NSDC Disbursement Load Report'),
(NULL, 'disbursemenfile', @flag_id, 'Display BOI NSDC Disbursement files report');

SET @group_id := 3;

SET @priv_id := (SELECT id FROM `t_privileges` where name ='disbursementload' AND flag_id = @flag_id); 
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @group_id, @flag_id, @priv_id, 1);

SET @priv_id := (SELECT id FROM `t_privileges` where name ='disbursementreport' AND flag_id = @flag_id); 
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @group_id, @flag_id, @priv_id, 1);

SET @priv_id := (SELECT id FROM `t_privileges` where name ='exportdisbursementreport' AND flag_id = @flag_id); 
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @group_id, @flag_id, @priv_id, 1);

SET @priv_id := (SELECT id FROM `t_privileges` where name ='disbursemenfile' AND flag_id = @flag_id); 
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @group_id, @flag_id, @priv_id, 1);



DROP TABLE IF EXISTS `boi_disbursement_batch`;
CREATE TABLE `boi_disbursement_batch` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int(11) unsigned DEFAULT NULL,
  `customer_master_id` int(11) unsigned DEFAULT NULL,
  `product_customer_id` int(11) unsigned DEFAULT NULL,
  `txn_identifier` varchar(10) DEFAULT NULL,
  `account_number` varchar(30) DEFAULT NULL,
  `ifsc_code` varchar(20) DEFAULT NULL,
  `aadhar_no` varchar(20) DEFAULT NULL,
  `amount` varchar(10) DEFAULT NULL,
  `currency` char(3) DEFAULT NULL,
  `narration` varchar(100) DEFAULT NULL,
  `wallet_code` varchar(10) DEFAULT NULL,
  `txn_no` varchar(20) NOT NULL,
  `card_type` char(1) NOT NULL,
  `corporate_id` varchar(20) NOT NULL,
  `mode` enum('cr','dr') DEFAULT 'cr',
  `bucket` char(2) DEFAULT NULL,
  `status` enum('active','inactive','temp') DEFAULT 'active',
  `disbursement_number` varchar(20) DEFAULT NULL,
  `batch_name` varchar(100) NOT NULL,
  `ttum_file_id` int(11) unsigned DEFAULT NULL,
  `date_updated` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `date_create` datetime DEFAULT NULL,
  `failed_reason` varchar(512) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `boi_disbursement_file`;
CREATE TABLE `boi_disbursement_file` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `file_name` varchar(100) DEFAULT NULL,
  `disbursement_no` varchar(10) DEFAULT NULL,
  `date_process` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `comment` varchar(255) NOT NULL DEFAULT '',
  `by_ops_id` int(11) unsigned NOT NULL,
  `status` enum('active','inactive','processed') CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT 'active',
  `date_updated` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `date_created` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



ALTER TABLE `boi_disbursement_file`
ADD COLUMN `wallet_file_name`  varchar(100) NULL AFTER `file_name`;
