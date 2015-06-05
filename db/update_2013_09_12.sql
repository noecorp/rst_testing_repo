ALTER TABLE `kotak_remit_remitters` ADD `legal_id` VARCHAR( 20 ) NULL AFTER `email`;

 CREATE TABLE `bank_statement` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `bank_stt_name` varchar(50) DEFAULT NULL,
  `txn_date` varchar(20) DEFAULT NULL,
  `description` varchar(100) DEFAULT NULL,
  `journal_no` varchar(50) DEFAULT NULL,
  `mode` enum('cr','dr') DEFAULT NULL,
  `amount` decimal(11,2) DEFAULT NULL,
  `balance` decimal(11,2) DEFAULT NULL,
  `is_duplicate` enum('yes','no') NOT NULL DEFAULT 'no',
  `is_settled` enum('yes','no') NOT NULL DEFAULT 'no',
  `by_ops_id` int(11) unsigned NOT NULL,
  `ip` varchar(15) NOT NULL,
  `date_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



ALTER TABLE `t_txn_ops`
ADD COLUMN `agent_funding_id`  int(11) UNSIGNED NULL AFTER `agent_fund_request_id`;

ALTER TABLE `t_txn_agent`
ADD COLUMN `agent_funding_id`  int(11) UNSIGNED NULL AFTER `agent_fund_request_id`;
