CREATE TABLE IF NOT EXISTS `hic_cardholders` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `unicode` bigint(20) unsigned NOT NULL,
  `crn` varchar(16) NOT NULL,
  `card_number` varchar(10) NOT NULL,
  `afn` varchar(10) NOT NULL,
  `medi_assist_id` varchar(10) NOT NULL,
  `emp_id` varchar(10) NOT NULL,
  `first_name` varchar(26) NOT NULL,
  `middle_name` varchar(26) NOT NULL,
  `last_name` varchar(26) NOT NULL,
  `gender` enum('male','female') NOT NULL,
  `date_of_birth` date NOT NULL,
  `mobile` varchar(15) NOT NULL,
  `email` int(100) DEFAULT NULL,
  `employer_name` int(100) NOT NULL,
  `corporate_id` int(11) unsigned NOT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;


CREATE TABLE IF NOT EXISTS `hic_cardholder_details` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `cardholder_id` int(11) unsigned NOT NULL,
  `unicode` bigint(20) unsigned NOT NULL,
  `crn` varchar(16) NOT NULL,
  `card_number` varchar(10) NOT NULL,
  `afn` varchar(10) NOT NULL,
  `medi_assist_id` varchar(10) NOT NULL,
  `emp_id` varchar(10) NOT NULL,
  `first_name` varchar(26) NOT NULL,
  `middle_name` varchar(26) NOT NULL,
  `last_name` varchar(26) NOT NULL,
  `gender` enum('male','female') NOT NULL,
  `date_of_birth` date NOT NULL,
  `mobile` varchar(15) NOT NULL,
  `email` int(100) DEFAULT NULL,
  `employer_name` int(100) NOT NULL,
  `corporate_id` int(11) unsigned NOT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;


INSERT INTO `t_flags` (`id`, `name`, `description`, `active_on_dev`, `active_on_prod`) VALUES (NULL, 'operation-hic_ratnakar', 'Health Insurance Claim - Ratnakar Bank Module', '1', '0');
SET @flag_id = last_insert_id();
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'index', @flag_id, 'Index Listing for HIC Ratnakar Bank'), (NULL, 'uploadcardholder', @flag_id, 'Bulk upload of cardholders for HIC Ratnakar Bank');

DROP TABLE `t_login_log`;
