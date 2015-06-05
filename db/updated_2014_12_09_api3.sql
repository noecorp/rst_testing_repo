ALTER TABLE `rat_corp_cardholders` CHANGE `txnrefnum` `txnrefnum` VARCHAR(20) NOT NULL;
ALTER TABLE `rat_beneficiaries` CHANGE `txnrefnum` `txnrefnum` VARCHAR( 20 ) CHARACTER SET latin1 COLLATE latin1_spanish_ci NOT NULL ;

CREATE TABLE IF NOT EXISTS `rat_debit_detail` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `bank_id` int(11) unsigned NOT NULL,
  `product_id` int(11) unsigned NOT NULL,
  `customer_master_id` int(11) unsigned NOT NULL,
  `customer_purse_id` int(11) unsigned NOT NULL,
  `purse_master_id` int(11) unsigned NOT NULL,
  `txn_code` int(10) unsigned NOT NULL,
  `txn_type` char(4) CHARACTER SET latin1 COLLATE latin1_spanish_ci DEFAULT NULL,
  `debit_id` int(11) unsigned NOT NULL,
  `amount` decimal(11,2) NOT NULL DEFAULT '0.00',
  `txn_status` enum('success','failure') CHARACTER SET latin1 COLLATE latin1_spanish_ci NOT NULL DEFAULT 'success',
  `date_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;