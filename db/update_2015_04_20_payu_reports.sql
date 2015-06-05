/*************
 *************
 *************  Wallet to wallet transfer report in OPS Portal
 *************
 *************/
SET @group_id := 3;
SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-reports'); 
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'w2wtransfer', @flag_id, 'Wallet to wallet transfer report');
SET @priv_id = last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @group_id, @flag_id, @priv_id, '1');



/*************
 *************
 *************  Export Wallet to wallet transfer report in OPS Portal
 *************
 *************/
SET @group_id := 3;
SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-reports'); 
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'exportw2wtransfer', @flag_id, 'Export Wallet to wallet transfer report');
SET @priv_id = last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @group_id, @flag_id, @priv_id, '1');




/*************
 *************
 *************  Wallet to wallet transfer report
 *************
 *************/
SET @flag_id := (SELECT id FROM `t_flags` WHERE name='agent-reports');
INSERT INTO `t_privileges` (`id` ,`name` ,`flag_id` ,`description`) VALUES (NULL , 'w2wtransfer', @flag_id, 'Wallet to wallet transfer report');
SET @privilege_id = last_insert_id();
INSERT INTO `t_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) SELECT NULL,id,@flag_id, @privilege_id,'1' FROM `t_products` WHERE `program_type` IN ('DigiWallet');


/*************
 *************
 *************  Export Wallet to wallet transfer report
 *************
 *************/
SET @flag_id := (SELECT id FROM `t_flags` WHERE name='agent-reports');
INSERT INTO `t_privileges` (`id` ,`name` ,`flag_id` ,`description`) VALUES (NULL , 'exportw2wtransfer', @flag_id, 'Export Wallet to wallet transfer report');
SET @privilege_id = last_insert_id();
INSERT INTO `t_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) SELECT NULL,id,@flag_id, @privilege_id,'1' FROM `t_products` WHERE `program_type` IN ('DigiWallet');