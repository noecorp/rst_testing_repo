CREATE TABLE `bank_users` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `bank_id` int(11) unsigned NOT NULL,
  `first_name` varchar(20) NOT NULL,
  `last_name` varchar(40) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(40) NOT NULL,
  `email` varchar(340) NOT NULL,
  `mobile` varchar(20) NOT NULL,
  `last_login` timestamp NULL DEFAULT NULL,
  `last_password_update` timestamp NULL DEFAULT '0000-00-00 00:00:00',
  `auth_code` varchar(20) NOT NULL,
  `num_login_attempts` tinyint(4) NOT NULL DEFAULT '0',
  `session_id` varchar(30) NOT NULL,
  `by_ops_id` int(11) unsigned NOT NULL,
  `date_created` datetime NOT NULL,
  `date_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `is_logged` enum('yes','no') NOT NULL DEFAULT 'no',
  `status` enum('active','inactive','locked') NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_username` (`username`) USING BTREE,
  KEY `idx_email` (`email`(255)) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `bind_bank_user_product` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `bank_user_id` int(11) unsigned NOT NULL,
  `bank_id` int(11) unsigned NOT NULL,
  `product_id` int(11) unsigned NOT NULL,
  `by_ops_id` int(11) unsigned NOT NULL,
  `date_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_start` date NOT NULL,
  `date_end` date NOT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
