SET @ops_id := 3;

INSERT INTO `t_flags` (`name`, `description`, `active_on_dev`, `active_on_prod`) VALUES ('operation-corp_kotak_cardload', 'Kotak Amul Cardload', '1', '0');
SET @flag_id = last_insert_id();

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'cardload', @flag_id, 'Card Load');
SET @priv_id := (SELECT LAST_INSERT_ID());
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');


INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'walletstatus', @flag_id, 'Wallet Status');
SET @priv_id := (SELECT LAST_INSERT_ID());
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');


INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'exportwalletstatus', @flag_id, 'Wallet Status');
SET @priv_id := (SELECT LAST_INSERT_ID());
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');



CREATE TABLE IF NOT EXISTS `kotak_corp_load_request` (
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
-- Table structure for table `kotak_corp_load_request_batch`
--

CREATE TABLE IF NOT EXISTS `kotak_corp_load_request_batch` (
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
-- Table structure for table `kotak_txn_customer`
--

CREATE TABLE IF NOT EXISTS `kotak_txn_customer` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `txn_code` int(11) unsigned NOT NULL,
  `customer_master_id` int(11) unsigned NOT NULL,
  `txn_customer_master_id` int(11) unsigned NOT NULL,
  `txn_agent_id` int(11) unsigned NOT NULL,
  `txn_ops_id` int(11) unsigned NOT NULL,
  `product_id` int(11) unsigned NOT NULL,
  `insurance_claim_id` int(11) unsigned NOT NULL,
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
  UNIQUE KEY `txn_code` (`txn_code`,`mode`,`txn_type`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;




INSERT INTO `t_cron` (`id`, `name`, `description`, `file_name`, `status`, `status_cron`, `date_updated`) 
VALUES ('23', 'Kotak Corp Corporate Load', 'Cron will load Kotak Corp Amul customer with ECS for wallet', 'KotakCorporateLoad.php', 'active', 'completed', CURRENT_TIMESTAMP);


INSERT INTO t_transaction_type (typecode, name, status, is_comm) VALUES ('KCCL', 'Kotak Corporate CardLoad', 'active' , 'no');

SET @flag_id := (SELECT id FROM `t_flags` WHERE name = 'operation-corp_kotak_customer');

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'bankstatus', @flag_id, 'Bank Application Status');
SET @priv_id := (SELECT LAST_INSERT_ID());
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'resubmit', @flag_id, 'Resubmit rejected application ');
SET @priv_id := (SELECT LAST_INSERT_ID());
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');
