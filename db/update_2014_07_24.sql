
ALTER TABLE `rat_corp_cardholders`  ADD `aadhaar_no` VARCHAR(20) NOT NULL AFTER `date_of_birth`,  ADD `pan` VARCHAR(10) NOT NULL AFTER `aadhaar_no`;

ALTER TABLE `rat_corp_load_request` ADD `employee_id` VARCHAR( 15 ) NULL AFTER `medi_assist_id` ;
ALTER TABLE `rat_corp_load_request_batch` ADD `employee_id` VARCHAR( 15 ) NULL AFTER `medi_assist_id` ;

ALTER TABLE `rat_corp_cardholders` CHANGE `status` `status` ENUM('active','inactive','ecs_pending','ecs_failed','blocked','activation_pending') CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT 'ecs_pending';