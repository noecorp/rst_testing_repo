ALTER TABLE `purse_master`
ADD COLUMN `priority`  int(11) UNSIGNED NOT NULL AFTER `txn_max_val_yearly`;

UPDATE purse_master SET priority = 1 WHERE code = 'RCI310' LIMIT 1;
UPDATE purse_master SET priority = 2 WHERE code = 'RCH310' LIMIT 1;