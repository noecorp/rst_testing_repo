ALTER TABLE `t_txn_agent` DROP INDEX `txn_code`, ADD UNIQUE `txn_code` (`txn_code`, `mode`, `txn_type`, `agent_id`, `purse_master_id`)COMMENT '';

ALTER TABLE `rat_txn_customer` DROP INDEX `txn_code` ,ADD UNIQUE `txn_code` ( `txn_code` , `mode` , `txn_type`, `purse_master_id` ) COMMENT '';