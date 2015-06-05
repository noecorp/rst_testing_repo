SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-remit_ratnakar_remitter'); 
SET @ops_id = '3';

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'uploadpaymenthistory', @flag_id, 'Upload payment History File');
SET @priv_id = last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'uploadresponsefile', @flag_id, 'Upload Response File');
SET @priv_id = last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');


ALTER TABLE `rat_remittance_request` ADD `utr` VARCHAR( 16 ) NULL AFTER `txn_code` ,
ADD `date_utr` DATETIME NULL AFTER `utr` ,
ADD `status_utr` ENUM( 'mapped', 'pending' ) NOT NULL DEFAULT 'pending' AFTER `date_utr` ,
ADD `date_status_response` DATETIME NOT NULL AFTER `status_utr` ,
ADD `status_response` ENUM( 'processed', 'rejected', 'pending' ) NOT NULL DEFAULT 'pending' AFTER `date_status_response` ;


DROP TABLE IF EXISTS `rat_payment_history`;
CREATE TABLE IF NOT EXISTS `rat_payment_history` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `txn_id` int(11) unsigned DEFAULT NULL,
  `ref_no` varchar(16) NOT NULL,
  `from_account_no` varchar(20) NOT NULL,
  `bene_account_no` varchar(20) NOT NULL,
  `bene_name` varchar(45) NOT NULL,
  `amount` varchar(10) NOT NULL,
  `transaction_status` varchar(15) NOT NULL,
  `core_status` varchar(15) NOT NULL,
  `narration` varchar(25) NOT NULL,
  `type_of_transaction` varchar(15) NOT NULL,
  `ifsc_code` varchar(20) NOT NULL,
  `utr` varchar(20) NOT NULL,
  `txn_code` varchar(10) NOT NULL,
  `date_transaction` datetime NOT NULL,
  `date_execution` datetime NOT NULL,
  `file_name` varchar(25) NOT NULL,
  `date_created` datetime NOT NULL,
  `date_updated` datetime NOT NULL,
  `status` enum('mapped','pending') NOT NULL DEFAULT 'pending',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;


 DROP TABLE IF EXISTS `rat_response_file`;
CREATE TABLE IF NOT EXISTS `rat_response_file` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `payment_ref_no` varchar(25) NOT NULL,
  `utr` varchar(20) NOT NULL,
  `tran_id` varchar(10) NOT NULL,
  `value_date` varchar(10) NOT NULL,
  `batch_time` varchar(15) NOT NULL,
  `sender_ifsc` varchar(15) NOT NULL,
  `sender_name` varchar(25) NOT NULL,
  `sender_account_no` varchar(20) NOT NULL,
  `bene_ifsc` varchar(15) NOT NULL,
  `bene_name` varchar(25) NOT NULL,
  `bene_account_no` varchar(20) NOT NULL,
  `amount` varchar(10) NOT NULL,
  `status` varchar(10) NOT NULL,
  `rejection_code` varchar(15) NOT NULL,
  `rejection_remark` varchar(30) NOT NULL,
  `returned_date` varchar(10) NOT NULL,
  `file_name` varchar(25) NOT NULL,
  `date_created` datetime NOT NULL,
  `date_updated` datetime NOT NULL,
  `status_response` enum('mapped','pending') NOT NULL DEFAULT 'pending',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;



INSERT INTO `t_cron` (`id`, `name`, `description`, `file_name`, `status`, `status_cron`, `date_updated`) VALUES ('45', 'NEFT Payment History UTR mapping', 'NEFT Payment History UTR mapping', 'RatNEFTUtrMapping.php', 'active', 'completed', CURRENT_TIMESTAMP);
INSERT INTO `t_cron` (`id`, `name`, `description`, `file_name`, `status`, `status_cron`, `date_updated`) VALUES ('46', 'NEFT Response mapping', 'NEFT Response mapping', 'RatNEFTresponseMapping.php', 'active', 'completed', CURRENT_TIMESTAMP);

SET @product_id := (SELECT id FROM `t_products` WHERE unicode='914');
INSERT INTO `t_product_privileges` (`product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (@product_id, '33', '433', 1);
INSERT INTO `t_product_privileges` (`product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (@product_id, '33', '434', 1);