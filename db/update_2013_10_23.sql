
CREATE TABLE `rat_corp_load_request` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int(11) unsigned NOT NULL,
  `customer_master_id` int(11) unsigned NOT NULL,
  `cardholder_id` int(11) unsigned NOT NULL,
  `customer_purse_id` int(11) unsigned NOT NULL,
  `load_channel` enum('medi-assist','ops') NOT NULL,
  `txn_identifier_type` char(3) NOT NULL,
  `card_number` varchar(30) NOT NULL,
  `medi_assist_id` varchar(10) NOT NULL,
  `amount` decimal(11,2) NOT NULL,
  `currency` char(3) NOT NULL,
  `narration` varchar(50) NOT NULL,
  `wallet_code` enum('GNRL','MEDI','NA') NOT NULL DEFAULT 'NA',
  `txn_no` varchar(30) NOT NULL,
  `card_type` char(1) NOT NULL,
  `corporate_id` varchar(50) NOT NULL,
  `mode` enum('dr','cr') NOT NULL,
  `txn_code` int(11) unsigned NOT NULL,
  `by_agent_id` int(11) unsigned NOT NULL,
  `by_ops_id` int(11) unsigned NOT NULL,
  `ip` varchar(16) NOT NULL,
  `date_created` datetime DEFAULT NULL,
  `date_load` datetime NOT NULL,
  `date_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `status` enum('pending','loaded','failed','cutoff','blocked','completed','incomplete') NOT NULL DEFAULT 'pending',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


ALTER TABLE `rat_corp_cardholder_batch` ADD `card_number` VARCHAR( 16 ) NOT NULL AFTER `product_id` ,
ADD `card_pack_id` VARCHAR( 20 ) NOT NULL AFTER `card_number`;

ALTER TABLE `rat_corp_cardholders` ADD `card_pack_id` VARCHAR( 20 ) NOT NULL AFTER `card_number`;

SET @flag_id := (SELECT id FROM `t_flags` WHERE name = 'operation-corp_ratnakar_cardholder');

INSERT INTO `t_flags` (`id`, `name`, `description`, `active_on_dev`, `active_on_prod`) VALUES (NULL, 'operation-corp_ratnakar_cardload', 'Card load of corp customers', '1', '0');
SET @flag_id = last_insert_id();

INSERT INTO `t_privileges` (`id` ,`name` ,`flag_id` ,`description`)
VALUES (NULL , 'corporateload', @flag_id, 'Upload Corporate wallet load file');



DROP TABLE IF EXISTS `rat_corp_load_request_batch`;
CREATE TABLE IF NOT EXISTS `rat_corp_load_request_batch` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int(11) unsigned NOT NULL,
  `txn_identifier_type` char(3) NOT NULL,
  `card_number` varchar(30) NOT NULL,
  `medi_assist_id` varchar(10) NOT NULL,
  `amount` decimal(11,2) NOT NULL,
  `currency` char(3) NOT NULL,
  `narration` varchar(50) NOT NULL,
  `wallet_code` enum('GNRL','MEDI','NA') NOT NULL DEFAULT 'NA',
  `card_type` char(1) NOT NULL,
  `mode` enum('dr','cr') NOT NULL,
  `corporate_id` varchar(16) NOT NULL,
  `by_ops_id` int(11) unsigned NOT NULL,
  `ip` varchar(15) NOT NULL,
  `date_created` datetime DEFAULT NULL,
  `date_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `status` enum('temp','incomplete','pass','duplicate') NOT NULL DEFAULT 'temp',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

ALTER TABLE `rat_corp_load_request_batch` ADD `txn_no` VARCHAR( 30 ) NOT NULL AFTER `wallet_code`;

ALTER TABLE `rat_corp_load_request`
ADD COLUMN `purse_master_id`  int(11) UNSIGNED NOT NULL AFTER `cardholder_id`;

ALTER TABLE `rat_corp_load_request_batch` CHANGE `wallet_code` `wallet_code` ENUM( 'gnrl', 'medi', 'na' ) NOT NULL DEFAULT 'na';

ALTER TABLE `rat_corp_load_request_batch` ADD `batch_name` VARCHAR( 100 ) NOT NULL AFTER `ip`;

ALTER TABLE `rat_corp_load_request` ADD `batch_name` VARCHAR( 100 ) NOT NULL AFTER `ip` ;
ALTER TABLE `rat_corp_load_request_batch` CHANGE `status` `upload_status` ENUM( 'temp', 'incomplete', 'pass', 'duplicate' ) NOT NULL DEFAULT 'temp';

