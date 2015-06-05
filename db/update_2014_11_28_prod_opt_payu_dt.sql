
ALTER TABLE `global_purse_master`
ADD COLUMN `debit_api_cr`  enum('pool_ac','payable_ac') NOT NULL DEFAULT 'payable_ac' AFTER `txn_max_val_yearly`;

ALTER TABLE `log_global_purse_master`
ADD COLUMN `debit_api_cr`  enum('pool_ac','payable_ac') NOT NULL DEFAULT 'payable_ac' AFTER `txn_max_val_yearly`;

ALTER TABLE `purse_master`
ADD COLUMN `debit_api_cr`  enum('pool_ac','payable_ac') NOT NULL DEFAULT 'payable_ac' AFTER `txn_max_val_yearly`;

ALTER TABLE `log_purse_master`
ADD COLUMN `debit_api_cr`  enum('pool_ac','payable_ac') NOT NULL DEFAULT 'payable_ac' AFTER `txn_max_val_yearly`;

UPDATE purse_master SET debit_api_cr = 'pool_ac' WHERE code IN ('RCG910', 'RHG911') LIMIT 2;

ALTER TABLE `purse_master`
ADD COLUMN `payable_ac_id`  int(11) NOT NULL AFTER `debit_api_cr`;

ALTER TABLE `log_purse_master`
ADD COLUMN `payable_ac_id`  int(11) NOT NULL AFTER `debit_api_cr`;

update purse_master set payable_ac_id = id + 20;