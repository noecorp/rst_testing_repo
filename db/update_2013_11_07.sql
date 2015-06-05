ALTER TABLE `customer_master`
AUTO_INCREMENT=101;

DROP TABLE IF EXISTS `card_auth_request`;
CREATE TABLE `card_auth_request` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int(11) unsigned NOT NULL,
  `customer_master_id` int(11) unsigned NOT NULL,
  `cardholder_id` int(11) unsigned NOT NULL,
  `purse_master_id` int(11) unsigned NOT NULL,
  `customer_purse_id` int(11) unsigned NOT NULL,
  `card_number` varchar(30) NOT NULL,
  `tid` varchar(20) NOT NULL,
  `mcc_code` varchar(10) NOT NULL,
  `mid` varchar(20) NOT NULL,
  `amount_txn` decimal(14,2) NOT NULL,
  `amount_billed` decimal(14,2) NOT NULL,
  `currency_iso` char(3) NOT NULL,
  `narration` varchar(50) NOT NULL,
  `txn_no` varchar(30) NOT NULL,
  `mode` enum('dr','cr') NOT NULL,
  `rev_indicator` enum('y','n') NOT NULL DEFAULT 'n',
  `original_txn_no` varchar(30) DEFAULT NULL,
  `txn_code` int(11) unsigned NOT NULL,
  `date_created` datetime DEFAULT NULL,
  `date_reversal` datetime NOT NULL,
  `date_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `failed_reason` varchar(255) DEFAULT NULL,
  `status` enum('pending','failed','reversal','completed','incomplete') NOT NULL DEFAULT 'pending',
  `response_code` varchar(10) DEFAULT NULL,
  `response_msg` varchar(50) DEFAULT NULL,
  `status_ack` enum('y','n') NOT NULL DEFAULT 'n',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `card_auth_request_detail`;
CREATE TABLE `card_auth_request_detail` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `card_auth_request_id` int(11) unsigned NOT NULL,
  `product_id` int(11) unsigned NOT NULL,
  `customer_master_id` int(11) unsigned NOT NULL,
  `cardholder_id` int(11) unsigned NOT NULL,
  `purse_master_id` int(11) unsigned NOT NULL,
  `customer_purse_id` int(11) unsigned NOT NULL,
  `card_number` varchar(30) NOT NULL,
  `amount_txn` decimal(14,2) NOT NULL,
  `date_created` datetime DEFAULT NULL,
  `failed_reason` varchar(255) DEFAULT NULL,
  `status` enum('pending','failed','reversal','completed','incomplete') NOT NULL DEFAULT 'pending',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;

