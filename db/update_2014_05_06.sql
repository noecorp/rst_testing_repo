ALTER TABLE `bind_global_purse_mcc`
MODIFY COLUMN `date_created`  timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' AFTER `datetime_start`;