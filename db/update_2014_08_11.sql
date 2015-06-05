SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-remit_ratnakar_remitter'); 
SET @priv_id := (SELECT id FROM `t_privileges` where name ='manualrejection' AND flag_id=@flag_id); 
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, 8, @flag_id, @priv_id, '1');
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, 9, @flag_id, @priv_id, '1');