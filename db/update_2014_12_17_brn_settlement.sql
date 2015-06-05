ALTER TABLE `rat_corp_load_request` ADD `settlement_ref_no` VARCHAR( 30 ) NULL DEFAULT NULL AFTER `settlement_request_id` ;

ALTER TABLE `rat_settlement_response` CHANGE COLUMN `txn_code` `settlement_ref_no`  varchar(30) NOT NULL AFTER `sequence_no`;

