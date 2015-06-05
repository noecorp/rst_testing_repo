ALTER TABLE `t_remitters` ADD `profile_photo` VARCHAR( 100 ) NOT NULL AFTER `name`;

ALTER TABLE `t_change_status_log` ADD `beneficiary_id` INT( 11 ) UNSIGNED NOT NULL AFTER `bank_id`;

ALTER TABLE `t_agent_details` ADD `auth_email` VARCHAR( 100 ) NOT NULL AFTER `email` ;

SET @flag_id := (SELECT id FROM `t_flags` WHERE name='operation-reports');
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'loginsummary', @flag_id, 'Login summary report for Agent and Operation');
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'exportloginsummary', @flag_id, 'Export Login summary report for Agent and Operation');


DROP TABLE IF EXISTS `log_master`;
CREATE TABLE `log_master` (
  `date_stamped` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `by_id` int(11) unsigned DEFAULT NULL,
  `by_whom` enum('customer','bank','corporate','agent','helpdesk','ops') DEFAULT NULL,
  `functionality` varchar(100) DEFAULT NULL,
  `session_id` varchar(100) DEFAULT NULL,
  `table_name` varchar(100) DEFAULT NULL,
  `txt_old` text,
  `txt_new` text,
  `remarks` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
