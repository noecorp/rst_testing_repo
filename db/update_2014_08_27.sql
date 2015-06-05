ALTER TABLE `rat_payment_history` CHANGE `status` `status` ENUM('mapped','pending','failed') CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT 'pending';

ALTER TABLE `rat_payment_history` CHANGE `upload_status` `upload_status` ENUM('success','failed','pending') CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT 'pending';

SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-remit_ratnakar_remitter'); 
SET @ops_id = '3';


INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'exportpaymenthistoryreport', @flag_id, 'Export Payment History Report');
SET @priv_id = last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

SET @flag_id := (SELECT id FROM `t_flags` where name ='agent-remit_ratnakar_reports'); 
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'distributorremittancereport', @flag_id, 'Distributor Remittance Report');
SET @priv_id = last_insert_id();
SET @product_id := (SELECT id FROM `t_products` WHERE unicode='914');
INSERT INTO `t_product_privileges` (`product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (@product_id, @flag_id, @priv_id, 1);

SET @flag_id := (SELECT id FROM `t_flags` where name ='agent-remit_ratnakar_reports'); 
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'exportdistributorremittancereport', @flag_id, 'Export Distributor Remittance Report');
SET @priv_id = last_insert_id();
SET @product_id := (SELECT id FROM `t_products` WHERE unicode='914');
INSERT INTO `t_product_privileges` (`product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (@product_id, @flag_id, @priv_id, 1);

SET @flag_id := (SELECT id FROM `t_flags` where name ='agent-remit_ratnakar_reports'); 
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'superdistributorremittancereport', @flag_id, 'Super Distributor Remittance Report');
SET @priv_id = last_insert_id();
SET @product_id := (SELECT id FROM `t_products` WHERE unicode='914');
INSERT INTO `t_product_privileges` (`product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (@product_id, @flag_id, @priv_id, 1);

SET @flag_id := (SELECT id FROM `t_flags` where name ='agent-remit_ratnakar_reports'); 
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'exportsuperdistributorremittancereport', @flag_id, 'Export Super Distributor Remittance Report');
SET @priv_id = last_insert_id();
SET @product_id := (SELECT id FROM `t_products` WHERE unicode='914');
INSERT INTO `t_product_privileges` (`product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (@product_id, @flag_id, @priv_id, 1);

ALTER TABLE `t_agents` ADD `last_auth_code_update` TIMESTAMP NULL AFTER `auth_code`;

SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-corp_ratnakar_cardholder'); 
SET @ops_id = '3';

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'kycupgradation', @flag_id, 'KYC Upgradation');
SET @priv_id = last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'exportkycupgradation', @flag_id, 'KYC Upgradation Report');
SET @priv_id = last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

