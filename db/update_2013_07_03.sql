ALTER TABLE `hic_cardholders` ADD `by_ops_id` INT( 11 ) UNSIGNED NOT NULL AFTER `corporate_id` ,
ADD `date_created` DATETIME NOT NULL AFTER `by_ops_id`;
ALTER TABLE `hic_cardholder_details` ADD `ip` BIGINT( 20 ) UNSIGNED NOT NULL AFTER `corporate_id` ,
ADD `by_ops_id` INT( 11 ) UNSIGNED NOT NULL AFTER `ip` ,
ADD `date_created` DATETIME NOT NULL AFTER `by_ops_id`;
ALTER TABLE `hic_cardholder_details` CHANGE `email` `email` VARCHAR(100) NOT NULL, CHANGE `employer_name` `employer_name` VARCHAR(100) NOT NULL, CHANGE `corporate_id` `corporate_id` VARCHAR(11) NOT NULL;
ALTER TABLE `hic_cardholders` CHANGE `email` `email` VARCHAR(100) NOT NULL, CHANGE `employer_name` `employer_name` VARCHAR(100) NOT NULL, CHANGE `corporate_id` `corporate_id` VARCHAR(11) NOT NULL;
ALTER TABLE `hic_cardholders` CHANGE `date_created` `date_created` TIMESTAMP NULL DEFAULT NULL;
ALTER TABLE `hic_cardholder_details` CHANGE `date_created` `date_created` TIMESTAMP NULL DEFAULT NULL ;
ALTER TABLE `hic_cardholders` ADD `date_updated` TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `date_created`;
ALTER TABLE `hic_cardholder_details` ADD `date_updated` TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `date_created`;

SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-hic_ratnakar_cardholder');

INSERT INTO `t_privileges` (`name`, `flag_id`, `description`) VALUES ('view', @flag_id, 'View CardHolder details');
ALTER TABLE `hic_cardholders`  ADD `batch_name` VARCHAR(100) NOT NULL AFTER `corporate_id`;

CREATE TABLE IF NOT EXISTS `corporate_master` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ecs_corp_id` int(11) unsigned NOT NULL,
  `name` varchar(100) NOT NULL,
  `address` varchar(255) NOT NULL,
  `city` varchar(100) NOT NULL,
  `state` varchar(100) NOT NULL,
  `pincode` varchar(10) NOT NULL,
  `contact_number` varchar(100) NOT NULL,
  `contact_email` varchar(100) NOT NULL,
  `by_ops_id` int(11) unsigned NOT NULL,
  `ip` varchar(16) NOT NULL,
  `date_created` timestamp NULL DEFAULT NULL,
  `date_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;


CREATE TABLE IF NOT EXISTS `hic_insurance_claim` (
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

ALTER TABLE `t_remitters` ADD `profile_photo` VARCHAR( 100 ) NOT NULL AFTER `name`;
