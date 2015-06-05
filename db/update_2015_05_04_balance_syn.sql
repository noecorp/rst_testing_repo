SET @group_id := 3;
SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-corp_ratnakar_reports'); 
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'balancesyncexception', @flag_id, 'Balance Sync Exception Report');
SET @priv_id = last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @group_id, @flag_id, @priv_id, '1');

SET @group_id := 3;
SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-corp_ratnakar_reports'); 
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'exporbalancesyncexception', @flag_id, 'Export Balance Sync Exception Report');
SET @priv_id = last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @group_id, @flag_id, @priv_id, '1');
