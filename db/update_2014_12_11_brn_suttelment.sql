SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-corp_ratnakar_cardload'); 
SET @ops_id = '3';

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'unsettlementrequests', @flag_id, 'Unsettled Instruction Batches');
SET @priv_id = last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');


SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-corp_ratnakar_cardload'); 
SET @ops_id = '3';
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'unsettlementbatchdetails', @flag_id, 'Unsettled Batch Details');
SET @priv_id = last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-corp_ratnakar_cardload'); 
SET @ops_id = '3';
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'unsettlementbatch', @flag_id, 'Unsettled Batch Creation');
SET @priv_id = last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');


