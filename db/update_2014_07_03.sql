ALTER TABLE `corporate_funding` CHANGE `txn_type` `txn_type` CHAR( 4 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT 'CGFL';


INSERT INTO `t_cron` (`id`, `name`, `description`, `file_name`, `status`, `status_cron`, `date_updated`) VALUES ('41', 'Update CRN for Rat GPR', 'Cron to update CRN for Rat GPR', 'RatGPRCorpCRNUpdate.php', 'active', 'completed', CURRENT_TIMESTAMP);


SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-corp_ratnakar_cardholder'); 
SET @ops_id = '3';

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'upgradekyc', @flag_id, 'Upgrade KYC');
SET @priv_id = last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'revertkyc', @flag_id, 'Revert KYC');
SET @priv_id = last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'upgradekycsearch', @flag_id, 'Upgrade KYC Search');
SET @priv_id = last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');


ALTER TABLE `rat_corp_cardholders` ADD `date_toggle_kyc` DATE NOT NULL AFTER `date_approval` ;

ALTER TABLE `rat_corp_log_cardholder` ADD `status_kyc_old` VARCHAR( 15 ) NULL AFTER `product_customer_id` ,
ADD `status_kyc_new` VARCHAR( 15 ) NULL AFTER `status_kyc_old` ;

UPDATE `t_cron` SET `file_name` = 'RatCorpCRNUpdate.php' WHERE `t_cron`.`id` =41;




INSERT INTO `t_cron` (`id`, `name`, `description`, `file_name`, `status`, `status_cron`, `date_updated`) VALUES (40, 'Remit Kotak Failure Recon ', 'Cron for Remit Kotak Failure Recon', 'RemitKotakFailureRecon.php', 'active', 'completed', CURRENT_TIMESTAMP);


SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-remit_reports');

SET @ops_id = '3';

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'remitkotakfailurerecon', @flag_id, 'Remit Kotak Failure Recon');
SET @priv_id = last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

ALTER TABLE `rat_corp_cardholders` ADD `date_crn_update` DATETIME NULL AFTER `date_failed` ,
ADD `status_ecs` ENUM( 'pending', 'failure', 'success', 'waiting' ) NOT NULL DEFAULT 'pending' AFTER `date_crn_update` ;