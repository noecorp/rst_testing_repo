ALTER TABLE `rat_corp_load_request` ADD `txn_identifier_num` VARCHAR(29) NULL AFTER `medi_assist_id`;


ALTER TABLE `rat_remittance_request` ADD `txnrefnum` VARCHAR(20) NULL AFTER `txn_code`;
