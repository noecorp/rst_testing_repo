CREATE TABLE IF NOT EXISTS `rat_hic_insurance_claim` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int(11) unsigned NOT NULL,
  `customer_id` int(11) unsigned NOT NULL,
  `cardholder_id` int(11) unsigned NOT NULL,
  `amount` decimal(11,2) NOT NULL,
  `hospital_id_code` int(11) unsigned NOT NULL,
  `txn_type` char(4) NOT NULL,
  `txn_code` int(11) unsigned NOT NULL,
  `by_agent_id` int(11) unsigned NOT NULL,
  `ip` varchar(16) NOT NULL,
  `date_created` timestamp NULL DEFAULT NULL,
  `date_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `num_fail_loads` tinyint(4) NOT NULL DEFAULT '0',
  `status` enum('pending','loaded','failed','cutoff') NOT NULL DEFAULT 'pending',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;