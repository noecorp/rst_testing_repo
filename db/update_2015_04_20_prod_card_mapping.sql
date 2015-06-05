ALTER TABLE `rat_wallet_transfer` CHANGE  `status` `status` ENUM( 'pending', 'success', 'failure', 'ecs_pending', 'ecs_failed', 'reversed',  'in_process' ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT 'pending';

ALTER TABLE  `rat_wallet_transfer` ADD  `failed_reason` VARCHAR( 255 ) NULL DEFAULT NULL AFTER  `txn_type`;

ALTER TABLE `rat_wallet_transfer` ADD `txn_credit_id` INT( 11 ) NOT NULL AFTER  `status`, ADD `txn_debit_id` INT( 11 ) NOT NULL AFTER  `txn_credit_id` ;

ALTER TABLE `rat_wallet_transfer` ADD  `date_ecs` DATETIME NULL DEFAULT NULL AFTER `failed_reason`;
