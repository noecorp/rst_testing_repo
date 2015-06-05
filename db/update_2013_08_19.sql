DROP TABLE IF EXISTS `rat_customer_purse`;
CREATE TABLE IF NOT EXISTS `rat_customer_purse` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `rat_customer_id` int(11) unsigned DEFAULT NULL,
  `purse_master_id` int(11) unsigned NOT NULL,
  `product_id` int(11) unsigned DEFAULT NULL,
  `bank_id` int(11) unsigned DEFAULT NULL,
  `amount` decimal(11,2) NOT NULL,
  `block_amount` decimal(11,2) NOT NULL,
  `date_updated` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='amount is balance amount' AUTO_INCREMENT=1 ;