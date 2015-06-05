ALTER TABLE `rat_corp_load_request_detail` ADD `adjust_id` INT NOT NULL AFTER `txn_processing_id` ;
ALTER TABLE `rat_corp_load_request_detail` CHANGE `adjust_id` `adjust_id` INT( 11 ) UNSIGNED NULL DEFAULT NULL ;
