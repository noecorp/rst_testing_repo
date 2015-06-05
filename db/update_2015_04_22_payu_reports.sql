/*************
 *************
 *************  Wallet to wallet transfer report in OPS Portal
 *************
 *************/
SET @group_id := 3;
SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-reports'); 
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'wwftexceptions', @flag_id, 'Wallet to wallet transfer Exceptions report');
SET @priv_id = last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @group_id, @flag_id, @priv_id, '1');



/*************
 *************
 *************  Export Wallet to wallet transfer report in OPS Portal
 *************
 *************/
SET @group_id := 3;
SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-reports'); 
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'exportwwftexceptions', @flag_id, 'Export Wallet to wallet transfer Exceptions report');
SET @priv_id = last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @group_id, @flag_id, @priv_id, '1');
 

/*************
 *************
 *************  Alter Scripts that will enable rat_wallet_transfer status to in_process
 *************	
 *************/

ALTER TABLE `rat_wallet_transfer` CHANGE  `status` `status` ENUM( 'pending', 'success', 'failure', 'ecs_pending', 'ecs_failed', 'reversed',  'in_process' ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT 'pending';
ALTER TABLE  `rat_wallet_transfer` ADD  `failed_reason` VARCHAR( 255 ) NULL DEFAULT NULL AFTER  `original_txn_no`;
ALTER TABLE `rat_wallet_transfer` ADD `txn_credit_id` INT( 11 ) NOT NULL AFTER  `status`, ADD `txn_debit_id` INT( 11 ) NOT NULL AFTER  `txn_credit_id` ;
ALTER TABLE `rat_wallet_transfer` ADD  `date_ecs` DATETIME NULL DEFAULT NULL AFTER `failed_reason`;