ALTER TABLE `rat_corp_load_request_batch` CHANGE `upload_status` `upload_status` ENUM( 'temp', 'incomplete', 'pass', 'duplicate', 'rejected' ) NOT NULL DEFAULT 'temp';

UPDATE t_cron SET name = 'Corporate Load in wallet HR', 
description = 'Cron will load medi assist customer with ECS for wallet HR', 
file_name = 'RatCorporateLoad.php', status = 'active', status_cron = 'completed' WHERE id = 16 LIMIT 1;

ALTER TABLE `t_txn_ops`
ADD COLUMN `txn_customer_master_id`  int(11) UNSIGNED NOT NULL AFTER `ops_id`,
ADD COLUMN `purse_master_id`  int(11) UNSIGNED NOT NULL AFTER `txn_customer_master_id`,
ADD COLUMN `customer_purse_id`  int(11) UNSIGNED NOT NULL AFTER `purse_master_id`;

ALTER TABLE `rat_txn_customer`
MODIFY COLUMN `txn_customer_master_id`  int(11) UNSIGNED NOT NULL AFTER `customer_master_id`,
MODIFY COLUMN `txn_agent_id`  int(11) UNSIGNED NOT NULL AFTER `txn_customer_master_id`,
MODIFY COLUMN `txn_ops_id`  int(11) UNSIGNED NOT NULL AFTER `txn_agent_id`,
MODIFY COLUMN `product_id`  int(11) UNSIGNED NOT NULL AFTER `txn_ops_id`,
MODIFY COLUMN `insurance_claim_id`  int(11) UNSIGNED NOT NULL AFTER `product_id`;