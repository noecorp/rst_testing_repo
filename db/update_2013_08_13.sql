INSERT INTO `t_cron` (`id`, `name`, `description`, `file_name`, `status`, `status_cron`, `date_updated`) VALUES (16, 'Load Medi Assist Customer', 'Cron will load medi assist customer with ECS', 'LoadMediAssistCustomer.php', 'active', 'completed', '2013-08-08 10:56:14');
DROP TABLE IF EXISTS `rat_customer_master`;
CREATE TABLE IF NOT EXISTS `rat_customer_master` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `customer_master_id` int(11) unsigned NOT NULL,
  `shmart_crn` int(11) unsigned DEFAULT NULL,
  `first_name` varchar(50) DEFAULT NULL,
  `middle_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) DEFAULT NULL,
  `aadhaar_no` varchar(20) DEFAULT NULL,
  `pan` varchar(10) DEFAULT NULL,
  `mobile_country_code` varchar(6) DEFAULT NULL,
  `mobile` varchar(15) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `gender` enum('male','female') DEFAULT 'male',
  `date_of_birth` date DEFAULT NULL,
  `status` enum('incomplete','pending','active','inactive') DEFAULT 'pending',
  PRIMARY KEY (`id`),
  UNIQUE KEY `unq_shmart_crn` (`shmart_crn`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `rat_customer_product` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `rat_customer_id` int(11) unsigned DEFAULT NULL,
  `product_id` int(11) unsigned DEFAULT NULL,
  `program_type` varchar(10) DEFAULT NULL,
  `bank_id` int(11) unsigned DEFAULT NULL,
  `by_agent_id` int(11) unsigned DEFAULT NULL,
  `by_ops_id` int(11) unsigned DEFAULT NULL,
  `date_created` timestamp NULL DEFAULT NULL,
  `date_updated` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `status` enum('active','inactive') DEFAULT 'active',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

