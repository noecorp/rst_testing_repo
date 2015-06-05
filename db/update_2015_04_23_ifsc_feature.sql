/*************
 *************
 *************  Manage Ifsc Code in OPS Portal
 *************
 *************/
SET @group_id := 3;
SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-settings'); 
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'manageifsc', @flag_id, 'Search IFSC Code here.');
SET @priv_id = last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @group_id, @flag_id, @priv_id, '1');



/*************
 *************
 *************  Add Ifsc Code in OPS Portal
 *************
 *************/
SET @group_id := 3;
SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-settings'); 
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'addifsc', @flag_id, 'Add IFSC Code here.');
SET @priv_id = last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @group_id, @flag_id, @priv_id, '1');




/*************
 *************
 *************  Update Ifsc Code in OPS Portal
 *************
 *************/
SET @group_id := 3;
SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-settings'); 
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'updateifsc', @flag_id, 'Update IFSC Code Page.');
SET @priv_id = last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @group_id, @flag_id, @priv_id, '1');
 