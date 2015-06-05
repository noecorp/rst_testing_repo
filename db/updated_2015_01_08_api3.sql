ALTER TABLE `t_txn_ops` DROP INDEX `txn_code`, ADD UNIQUE `txn_code` (`txn_code`, `mode`, `txn_type`, `ops_id`, `purse_master_id`)COMMENT '';
