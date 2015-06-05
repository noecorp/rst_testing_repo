SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-remit_kotak_remitter'); 
SET @ops_id = '8';-- Maker
SET @priv_id := (SELECT id FROM `t_privileges` where name ='index' AND flag_id=@flag_id); 
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

SET @ops_id = '9';-- Checker
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');


SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-corporatefunding'); 

SET @ops_id = '9';-- Checker
SET @priv_id := (SELECT id FROM `t_privileges` where name ='index' AND flag_id=@flag_id); 
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');


SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-settings'); 
SET @ops_id = '9';-- Checker
SET @priv_id := (SELECT id FROM `t_privileges` where name ='index' AND flag_id=@flag_id); 
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');
SET @ops_id = '8';-- Maker
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

