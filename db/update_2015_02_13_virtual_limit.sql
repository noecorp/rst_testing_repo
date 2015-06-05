  
/* ACM for virtualwalletbalance in reports Section*/
SET @flag_id := (SELECT id FROM `t_flags` WHERE `name` = 'operation-reports');   
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'virtualwalletbalance', @flag_id, 'Virtual Wallet Balance Report');
SET @privileg_id := last_insert_id();  
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 3, @flag_id, @privileg_id, 1);


/* ACM for exportvirtualwalletbalance in reports Section*/
SET @flag_id := (SELECT id FROM `t_flags` WHERE `name` = 'operation-reports');   
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'exportvirtualwalletbalance', @flag_id, 'Export Virtual Wallet Balance Report');
SET @privileg_id := last_insert_id();  
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 3, @flag_id, @privileg_id, 1);

