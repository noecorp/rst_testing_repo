ALTER TABLE `rat_txn_customer`
ADD COLUMN `date_updated`  timestamp NULL DEFAULT NULL AFTER `date_created`;

ALTER TABLE `t_txn_agent`
ADD COLUMN `date_updated`  timestamp NULL DEFAULT NULL AFTER `date_created`;

INSERT INTO `t_cron` (`id`, `name`, `description`, `file_name`, `status`, `status_cron`, `date_updated`) VALUES ('17', 'Clear Insurance Claim', 'Cron will update status as cutoff of insurance claim, if claim amount not used by cardholder by particular time duration basis', 'ClearInsuranceClaim.php', 'active', 'completed', CURRENT_TIMESTAMP);

