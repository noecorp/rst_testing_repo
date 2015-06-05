CREATE TABLE `card_txn_processing` (
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
  `status` enum('pending','failed','reversal','completed','incomplete', 'reversed') NOT NULL DEFAULT 'pending',
  `response_code` varchar(10) DEFAULT NULL,
  `response_msg` varchar(50) DEFAULT NULL,
  `status_ack` enum('y','n') NOT NULL DEFAULT 'n',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO t_transaction_type (typecode, name, status, is_comm) 
VALUES ('RRCT', 'Reversal Ratnakar Corporate Authentication & Transaction Processing', 'active', 'no');

INSERT INTO `t_cron` (`id` ,`name` ,`description` ,`file_name` ,`status` ,`status_cron` ,`date_updated`)
VALUES (19 , 'Transaction Intimation', 'Transaction Intimation to partner', 'TransactionIntimation', 'active', 'completed', CURRENT_TIMESTAMP );

SET @flag_id := (SELECT id FROM `t_flags` WHERE name='operation-reports');
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'wallettxn', @flag_id, 'Transaction Report Wallet-wise');
