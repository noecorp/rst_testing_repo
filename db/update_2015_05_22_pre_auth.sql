CREATE TABLE `block_amount` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `bank_id` int(11) unsigned NOT NULL,
  `product_id` int(11) unsigned NOT NULL,
  `customer_master_id` int(11) unsigned NOT NULL,
  `customer_purse_id` int(11) unsigned NOT NULL,
  `amount` decimal(14,2) unsigned NOT NULL DEFAULT '0.00',
  `txn_type` char(4) NOT NULL,
  `txn_code` int(11) unsigned DEFAULT NULL,
  `narration` varchar(50) DEFAULT NULL,
  `status` enum('blocked','unblocked','claimed','released','failed') NOT NULL DEFAULT 'blocked',
  `failed_reason` varchar(100) DEFAULT NULL,
  `date_created` datetime NOT NULL,
  `date_updated` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `date_unblocked` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `txn_code` (`txn_code`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1