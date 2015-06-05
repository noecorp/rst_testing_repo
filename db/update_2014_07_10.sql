ALTER TABLE `rat_remittance_status_log` CHANGE `status_new` `status_new` ENUM( 'in_process','processed','success','failure','refund','incomplete' ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT 'in_process';

INSERT INTO `t_cron` (`id`, `name`, `description`, `file_name`, `status`, `status_cron`, `date_updated`) VALUES ('44', 'NEFT Response send SMS', 'NEFT Response send SMS', 'RatNEFTResponseSendSms.php', 'active', 'completed', CURRENT_TIMESTAMP);
