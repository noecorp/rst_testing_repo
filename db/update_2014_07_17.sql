ALTER TABLE `rat_remittance_request` ADD `utr_by_ops_id` INT( 11 ) UNSIGNED NOT NULL AFTER `utr` ;
ALTER TABLE `rat_remittance_request` ADD `status_response_by_ops_id` INT( 11 ) UNSIGNED NOT NULL AFTER `status_response` ;

SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-remit_ratnakar_remitter'); 
SET @ops_id = '3';

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'manualmapping', @flag_id, 'Manual Mapping');
SET @priv_id = last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'manualmappingupdate', @flag_id, 'Manual Mapping update');
SET @priv_id = last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

SET @product_id := (SELECT id FROM `t_products` WHERE unicode='914');
INSERT INTO `t_product_privileges` (`product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (@product_id, '33', '433', 1);
INSERT INTO `t_product_privileges` (`product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (@product_id, '33', '434', 1);

ALTER TABLE `kotak_corp_load_request_batch` ADD `employee_id` VARCHAR( 10 ) NULL DEFAULT NULL AFTER `member_id` ;



SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-remit_ratnakar_remitter'); 
SET @ops_id = '3';


INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'uploadresponsepaymenthistory', @flag_id, 'Upload Response Payment File');
SET @priv_id = last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

ALTER TABLE `rat_remittance_request` ADD `manual_mapping_remarks` VARCHAR( 250 ) NULL AFTER `status_response_by_ops_id` ;

ALTER TABLE `rat_remittance_status_log` CHANGE `status_old` `status_old` ENUM( 'in_process', 'success', 'failure', 'incomplete', 'hold', 'refund', 'processed' ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT 'in_process',
CHANGE `status_new` `status_new` ENUM( 'in_process', 'success', 'failure', 'incomplete', 'hold', 'refund', 'processed', 'processed' ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT 'in_process';
