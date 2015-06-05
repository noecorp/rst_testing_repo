ALTER TABLE `t_remittance_request` ADD `neft_processed_ops_id` INT( 11 ) UNSIGNED NOT NULL AFTER `neft_processed` ,
ADD `neft_processed_date` DATETIME NOT NULL AFTER `neft_processed_ops_id`;
SET @flag_id := (SELECT id FROM `t_flags` WHERE name='operation-remit_boi_remitter');
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'neftprocessed', @flag_id, 'NEFT Processed');