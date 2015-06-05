
/*virtuallimit AND  editvirtuallimit*/
/*********** ACM UPDATE **************/

/*We delete privilege's and flipper's  entry for virtuallimit AND  editvirtuallimit */

/*Previously we was submitting virtual limit from OPS Portal But Now We only Accept and Reject virtual fund request From OPS portal Requested by Agents using Agent Portal*/

SET @old_flag_id := (SELECT id FROM `t_flags` WHERE `name` = 'operation-agents');
SET @old_privileg_id := (SELECT id FROM `t_privileges` WHERE `name` = 'virtuallimit' AND `flag_id` = @old_flag_id);
DELETE FROM `t_flippers` WHERE `flag_id` = @old_flag_id AND `privilege_id` = @old_privileg_id ;
DELETE FROM `t_privileges` WHERE `id` = @old_privileg_id ;


SET @old_flag_id := (SELECT id FROM `t_flags` WHERE `name` = 'operation-agents');
SET @old_privileg_id := (SELECT id FROM `t_privileges` WHERE `name` = 'editvirtuallimit' AND `flag_id` = @old_flag_id);
DELETE FROM `t_flippers` WHERE `flag_id` = @old_flag_id AND `privilege_id` = @old_privileg_id ;
DELETE FROM `t_privileges` WHERE `id` = @old_privileg_id ;




/***********ACM**************/


SET @flag_id := (SELECT id FROM `t_flags` WHERE `name` = 'operation-agentfunding');   
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'virtualfundrequests', @flag_id, 'Pending Agent Fund Requests For Virtual Balance');
SET @privileg_id := last_insert_id();  
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 3, @flag_id, @privileg_id, 1);



SET @flag_id := (SELECT id FROM `t_flags` WHERE `name` = 'operation-agentfunding');   
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'confirmvirtualfundrequset', @flag_id, 'Confirmation Page to Approve or reject Pending Agent Fund Requests of Virtual Balance');
SET @privileg_id := last_insert_id();  
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 3, @flag_id, @privileg_id, 1);
