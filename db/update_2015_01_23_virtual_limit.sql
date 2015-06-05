  
/***********ACM**************/


SET @flag_id := (SELECT id FROM `t_flags` WHERE `name` = 'operation-agentfunding');   
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'exportvirtualfundrequest', @flag_id, 'Export Pending Agent Fund Requests For Virtual Balance');
SET @privileg_id := last_insert_id();  
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 3, @flag_id, @privileg_id, 1);

 
 /***********ACM**************/
SET @flag_id := (SELECT id FROM `t_flags` WHERE `name` = 'operation-reports');   
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'unauthorizevirtualfund', @flag_id, 'Unauthorized Virtual Funding Reports');
SET @privileg_id := last_insert_id();  
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 3, @flag_id, @privileg_id, 1);

/***********ACM**************/
 
SET @flag_id := (SELECT id FROM `t_flags` WHERE `name` = 'operation-reports');   
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'exportunauthorizevirtualfund', @flag_id, 'Export Unauthorized Virtual Funding Reports');
SET @privileg_id := last_insert_id();  
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 3, @flag_id, @privileg_id, 1);