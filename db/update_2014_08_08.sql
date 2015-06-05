SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-remit_ratnakar_remitter'); 
SET @ops_id = '3';

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'manualrejection', @flag_id, 'RBL Manual Rejection');
SET @priv_id = last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');