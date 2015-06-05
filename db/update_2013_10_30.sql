INSERT INTO `t_flags` (`id`, `name`, `description`, `active_on_dev`, `active_on_prod`) VALUES (NULL, 'agent-linkedagents', 'Super Agent -  Linked agents', '1', '0');


SET @flag_id := (SELECT id FROM `t_flags` WHERE name = 'operation-corp_ratnakar_cardholder');

SET @priv_id := (SELECT id FROM `t_privileges` WHERE `flag_id` = @flag_id AND name ='uploadcrn');
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, '16', @flag_id, @priv_id, '1');


SET @flag_id := (SELECT id FROM `t_flags` WHERE name = 'operation-corp_ratnakar_cardload');

SET @priv_id := (SELECT id FROM `t_privileges` WHERE `flag_id` = @flag_id AND name ='corporateload');
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, '16', @flag_id, @priv_id, '1');

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'walletstatus', @flag_id, 'Wallet status report');
SET @priv_id := (SELECT id FROM `t_privileges` WHERE `flag_id` = @flag_id AND name ='walletstatus');
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, '16', @flag_id, @priv_id, '1');
