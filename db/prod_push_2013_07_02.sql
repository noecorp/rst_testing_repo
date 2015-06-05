DELETE FROM `t_bank_ifsc` WHERE bank_name = '' LIMIT 1;

DROP TABLE IF EXISTS `t_log_login`;
CREATE TABLE `t_log_login` (
  `cardholder_id` int(10) unsigned DEFAULT NULL,
  `agent_id` int(10) unsigned DEFAULT NULL,
  `ops_id` int(10) unsigned DEFAULT NULL,
  `bank_id` int(10) unsigned DEFAULT NULL,
  `ip` varchar(15) DEFAULT NULL,
  `datetime_login_step1` datetime DEFAULT NULL,
  `datetime_login_step2` datetime DEFAULT NULL,
  `datetime_logout` datetime DEFAULT NULL,
  `username` varchar(100) DEFAULT NULL,
  `comment_username` varchar(100) DEFAULT NULL,
  `comment_password` enum('success', 'failure') DEFAULT 'failure',
  `comment_auth` enum('success', 'failure', 'na') DEFAULT 'na',
  `session_id` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 ROW_FORMAT=COMPACT;

ALTER TABLE `t_log_login` ADD `date_updated` TIMESTAMP NOT NULL ;

ALTER TABLE `t_log_login` CHANGE `comment_auth` `comment_auth` VARCHAR( 100 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT 'na';

ALTER TABLE `t_log_login` ADD `portal` ENUM( 'operation', 'agent' ) NOT NULL FIRST;

DROP TABLE `t_login_log`;