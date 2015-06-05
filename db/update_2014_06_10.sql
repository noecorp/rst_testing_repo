DROP TABLE IF EXISTS `report_tpmis`;
CREATE TABLE IF NOT EXISTS `report_tpmis` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `tp_name` varchar(60) NOT NULL,
  `agent_name` varchar(60) NOT NULL,
  `tp_mobile` varchar(10) NOT NULL,
  `agent_mobile` varchar(10) NOT NULL,
  `tp_code` varchar(20) NOT NULL,
  `agent_code` int(20) NOT NULL,
  `wallet_load_from` date NOT NULL,
  `wallet_load_to` date NOT NULL,
  `file_name` varchar(50) NOT NULL,
  `date_request` datetime NOT NULL,
  `date_processed` datetime NOT NULL,
  `by_ops_id` int(11) unsigned NOT NULL,
  `remarks` varchar(250) NOT NULL,
  `status` enum('pending','started','failed','processed') NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;


ALTER TABLE `boi_disbursement_batch`
ADD COLUMN `payment_status`  enum('generated','processed','hold','manual','pending') NULL DEFAULT 'pending' AFTER `ttum_file_id`,
ADD COLUMN `wallet_status`  enum('pending','failed','success') NULL DEFAULT 'pending' AFTER `payment_status`,
ADD COLUMN `wallet_message`  varchar(100) NULL AFTER `wallet_status`;


DROP TABLE IF EXISTS `boi_disbursement_status_log`;
CREATE TABLE IF NOT EXISTS `boi_disbursement_status_log` (
  `disbursement_batch_id` int(11) unsigned NOT NULL,
  `status_type` varchar(40) DEFAULT NULL,
  `status` varchar(40) DEFAULT 'in_process',
  `note` text,
  `ttum_file_id` int(11) unsigned DEFAULT NULL,
  `by_ops_id` int(11) unsigned DEFAULT NULL,
  `date_created` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


INSERT INTO `t_cron` (`id`, `name`, `description`, `file_name`, `status`, `status_cron`, `date_updated`) VALUES ('37', 'Boi NSDC generate TP MIS Report Files', 'Cron will generate TP MIS Report files', 'BoiTpMisReport.php', 'active', 'completed', CURRENT_TIMESTAMP);

