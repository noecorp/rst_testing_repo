ALTER TABLE `corporate_balance` DROP `date_updated`;


SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-corp_kotak_cardholder'); 
SET @ops_id = '3';

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'kycupgradereport', @flag_id, 'KYC Upgrade Report');
SET @priv_id = last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'exportkycupgradereport', @flag_id, 'Export KYC Upgrade Report');
SET @priv_id = last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

ALTER TABLE `kotak_corp_cardholders` ADD `date_toggle_kyc` DATE NOT NULL AFTER `date_approval` ;
ALTER TABLE `kotak_corp_log_cardholder` ADD `status_kyc_old` VARCHAR( 15 ) NOT NULL AFTER `product_customer_id` ,
ADD `status_kyc_new` VARCHAR( 15 ) NOT NULL AFTER `status_kyc_old` ;
ALTER TABLE `kotak_corp_log_cardholder` CHANGE `status_kyc_old` `status_kyc_old` VARCHAR( 15 ) NULL ,
CHANGE `status_kyc_new` `status_kyc_new` VARCHAR( 15 ) NULL ;

SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-corp_kotak_cardholder');
SET @ops_id = '3';

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'upgradekycsearch', @flag_id, 'KYC upgradation Search');
SET @priv_id = last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'revertkyc', @flag_id, 'KYC status revert');
SET @priv_id = last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

ALTER TABLE `kotak_corp_cardholders` CHANGE `status` `status` ENUM( 'active', 'inactive', 'pending', 'ecs_failed', 'activation_pending','ecs_pending' ) NOT NULL DEFAULT 'ecs_pending';

ALTER TABLE `kotak_txn_customer` ADD `txn_corporate_id` INT( 11 ) UNSIGNED NOT NULL AFTER `txn_agent_id` ;

INSERT INTO `t_flags` (`id`, `name`, `description`, `active_on_dev`, `active_on_prod`) VALUES (NULL, 'corporate-corp_kotak_reports', 'Corporate Kotak reports', '1', '0');
SET @flag_id :=  last_insert_id();
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'index', @flag_id, 'Card load report');
SET @privileg_id :=  last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 4, @flag_id, @privileg_id, 1);
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'loadreport', @flag_id, 'Card load report');
SET @privileg_id :=  last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 4, @flag_id, @privileg_id, 1);
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'exportloadreport', @flag_id, 'Export Card load report');
SET @privileg_id :=  last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 4, @flag_id, @privileg_id, 1);
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'activecards', @flag_id, 'Active cards report');
SET @privileg_id :=  last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 4, @flag_id, @privileg_id, 1);

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'exportactivecards', @flag_id, 'Export Active cards report');
SET @privileg_id :=  last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 4, @flag_id, @privileg_id, 1);
