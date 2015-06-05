CREATE TABLE `t_customers` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `bank_id` int(11) unsigned NOT NULL,
  `mobile_country_code` varchar(6) DEFAULT NULL,
  `mobile` varchar(15) NOT NULL,
  `customer_type` enum('kyc','non-kyc') DEFAULT NULL,
  `status` enum('incomplete','pending','active','inactive') NOT NULL DEFAULT 'active',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


CREATE TABLE `customers_detail` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `bank_id` int(11) unsigned NOT NULL DEFAULT '0',
  `product_id` int(11) unsigned NOT NULL DEFAULT '0',
  `bank_customer_id` int(11) unsigned NOT NULL DEFAULT '0',
  `product_customer_id` int(11) unsigned NOT NULL,
  `date_created` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


ALTER TABLE `customer_master`
DROP COLUMN `first_name`,
DROP COLUMN `middle_name`,
DROP COLUMN `last_name`,
DROP COLUMN `email`;

ALTER TABLE `customers_detail`
ADD COLUMN `customer_id`  int(11) UNSIGNED NOT NULL AFTER `id`;

