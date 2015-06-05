ALTER TABLE `rat_corp_load_request`  ADD `status_settlement` ENUM('settled', 'unsettled', 'reverted') NOT NULL DEFAULT 'unsettled' AFTER `status`;
ALTER TABLE `rat_corp_load_request` ADD `date_settlement` DATETIME NOT NULL AFTER `status_settlement`;
ALTER TABLE `rat_corp_load_request` ADD `settlement_remarks` VARCHAR(255) NOT NULL AFTER `date_settlement`;

ALTER TABLE `rat_remittance_request`  ADD `status_settlement` ENUM('settled', 'unsettled', 'reverted') NOT NULL DEFAULT 'unsettled' AFTER `date_updated`;
ALTER TABLE `rat_remittance_request` ADD `date_settlement` DATETIME NOT NULL AFTER `status_settlement`;
ALTER TABLE `rat_remittance_request` ADD `settlement_remarks` VARCHAR(255) NOT NULL AFTER `date_settlement`;

SET @group_id := 3;
SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-corp_ratnakar_cardload'); 
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'uploadsettlement', @flag_id, 'API Upload Settlement Report');
SET @priv_id = last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @group_id, @flag_id, @priv_id, '1');


CREATE TABLE `rat_api_settlement` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `wallet_code` varchar(10) NOT NULL,
  `amount` varchar(10) NOT NULL,
  `txn_code` varchar(20) DEFAULT NULL,
  `date_created` datetime NOT NULL,
  `txn_identifier_type` char(10) NOT NULL, 
  `txn_identifier_num` varchar(50) NOT NULL,
  `mode` varchar(5) NOT NULL,
  `batch_name` varchar(100) NOT NULL,
  `product_id` int(11) unsigned NOT NULL,
  `load_request_id` int(11) unsigned NOT NULL,
  `remittance_request_id` int(11) unsigned NOT NULL,
  `by_ops_id` int(11) unsigned NOT NULL,
  `failed_reason` varchar(255) NOT NULL,
  `upload_status` enum('temp','pass','success','duplicate','rejected','failed') NOT NULL,
  `date_updated` timestamp ON UPDATE CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `rat_api_settlement`;
DROP TABLE IF EXISTS `rat_settlement_batch`;

CREATE TABLE `rat_settlement_batch` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int(11) unsigned NOT NULL,
  `wallet_code` varchar(10) NOT NULL,
  `amount` decimal(11,2) NOT NULL,
  `txn_code` int(11) unsigned NOT NULL,
  `mode` varchar(5) NOT NULL,
  `date_txn` date NOT NULL,
  `time_txn` time NOT NULL,
  `txn_identifier_type` char(3) NOT NULL,
  `txn_identifier_num` varchar(50) NOT NULL,
  `batch_name` varchar(100) NOT NULL,
  `by_ops_id` int(11) unsigned NOT NULL,
  `upload_status` enum('temp','pass','success','duplicate','rejected','failed') NOT NULL,
  `load_request_id` int(11) NOT NULL,
  `remittance_request_id` int(11) NOT NULL,
  `failed_reason` varchar(255) NOT NULL,
  `date_created` timestamp NULL DEFAULT NULL,
  `date_settled` timestamp NULL DEFAULT NULL,
  `date_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
