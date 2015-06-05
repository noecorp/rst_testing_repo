CREATE TABLE `boi_customer_master` (
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
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
