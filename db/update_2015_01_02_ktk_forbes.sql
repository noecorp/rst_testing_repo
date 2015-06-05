ALTER TABLE `kotak_beneficiaries` ADD `queryrefno` VARCHAR( 20 ) NULL DEFAULT NULL AFTER `bene_code`; 
ALTER TABLE `kotak_remittance_request` ADD `txnrefnum` VARCHAR(20) NULL AFTER `txn_code`;