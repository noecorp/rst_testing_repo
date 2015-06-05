ALTER TABLE `kotak_remittance_status_log`
ADD COLUMN `by_ops_id`  int(11) UNSIGNED NOT NULL AFTER `by_agent_id`;

ALTER TABLE `bank_statement`
ADD COLUMN `date_updated`  timestamp NULL AFTER `date_created`;


ALTER TABLE `agent_funding`
MODIFY COLUMN `status` enum('approve','pending','decline','duplicate') CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT 'pending' AFTER `settlement_remarks`;


ALTER TABLE `agent_funding`
MODIFY COLUMN `status` enum('approve','pending','decline','duplicate')  NOT NULL DEFAULT 'pending' AFTER `settlement_remarks`,
ADD COLUMN `bank_statement_id`  int(11) UNSIGNED NOT NULL AFTER `comments`;


ALTER TABLE `agent_funding` CHANGE `status` `status` ENUM( 'approved', 'pending', 'rejected', 'duplicate' ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT 'pending'
