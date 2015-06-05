CREATE TABLE `t_log_change_password` (
  `agent_id` int(11) unsigned NOT NULL,
  `ops_id` int(11) unsigned NOT NULL,
  `bank_id` int(11) unsigned NOT NULL,
  `ip` varchar(15) NOT NULL,
  `password` varchar(40) NOT NULL,
  `date_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

ALTER TABLE `t_agents` ADD COLUMN `last_password_update` timestamp NULL DEFAULT NULL AFTER `num_login_attempts`;
ALTER TABLE `t_agents` ADD COLUMN `last_login` timestamp NULL DEFAULT NULL AFTER `last_password_update`;