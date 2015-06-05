DROP TABLE IF EXISTS `rat_customer_closing_balance`;
CREATE TABLE IF NOT EXISTS `rat_customer_closing_balance` (
  `customer_master_id` int(11) unsigned NOT NULL,
  `rat_customer_id` int(11) unsigned NOT NULL,
  `purse_master_id` int(11) unsigned NOT NULL,
  `customer_purse_id` int(11) unsigned NOT NULL,
  `product_id` int(11) unsigned NOT NULL,
  `bank_id` int(11) unsigned NOT NULL,
  `closing_balance` decimal(11,2) NOT NULL,
  `date` date NOT NULL,
  `date_created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `date_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='amount is balance amount';