/*
 *   Add colum claim_txn_code
 */
ALTER TABLE `block_amount` ADD COLUMN `claim_txn_code` INT(11) UNSIGNED NULL DEFAULT NULL AFTER `narration`;


/*
 * Crone  (please update id according to constant CRON_BLOCK_AMOUNT_RELEASE_ID)
 */
INSERT INTO `t_cron` (`id`, `name`, `description`, `file_name`, `status`, `status_cron`, `date_updated`) VALUES (92, 'Block Amount  Release', 'Release Block Amount if time expiry', 'BlockAmountRelease.php', 'active', 'completed', '2015-05-27 10:30:23');


/*
 *  block_validity_hr (to release Amount)
 */
ALTER TABLE `purse_master` ADD COLUMN `block_validity_hr` TINYINT(4) UNSIGNED NOT NULL AFTER `is_virtual`;



/*
    WWFT Reports (same in `update_2015_04_20_payu_reports.sql`)  Start
*/
/**************  Wallet to wallet transfer report in OPS Portal*************/
SET @group_id := 3;
SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-reports'); 
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'w2wtransfer', @flag_id, 'Wallet to wallet transfer report');
SET @priv_id = last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @group_id, @flag_id, @priv_id, '1');

/**************  Export Wallet to wallet transfer report in OPS Portal *************/
SET @group_id := 3;
SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-reports'); 
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'exportw2wtransfer', @flag_id, 'Export Wallet to wallet transfer report');
SET @priv_id = last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @group_id, @flag_id, @priv_id, '1');

/**************  Wallet to wallet transfer report*************/
SET @flag_id := (SELECT id FROM `t_flags` WHERE name='agent-reports');
INSERT INTO `t_privileges` (`id` ,`name` ,`flag_id` ,`description`) VALUES (NULL , 'w2wtransfer', @flag_id, 'Wallet to wallet transfer report');
SET @privilege_id = last_insert_id();
INSERT INTO `t_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) SELECT NULL,id,@flag_id, @privilege_id,'1' FROM `t_products` WHERE `program_type` IN ('DigiWallet');

/**************  Export Wallet to wallet transfer report*************/
SET @flag_id := (SELECT id FROM `t_flags` WHERE name='agent-reports');
INSERT INTO `t_privileges` (`id` ,`name` ,`flag_id` ,`description`) VALUES (NULL , 'exportw2wtransfer', @flag_id, 'Export Wallet to wallet transfer report');
SET @privilege_id = last_insert_id();
INSERT INTO `t_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) SELECT NULL,id,@flag_id, @privilege_id,'1' FROM `t_products` WHERE `program_type` IN ('DigiWallet');

/*
    WWFT Reports (same in `update_2015_04_20_payu_reports.sql`) End
*/

