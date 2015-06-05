ALTER TABLE `t_txn_ops`
ADD COLUMN `kotak_remitter_id`  int(11) UNSIGNED NULL AFTER `txn_remitter_id`,
ADD COLUMN `kotak_remittance_request_id`  int(11) UNSIGNED NULL AFTER `remittance_request_id`;