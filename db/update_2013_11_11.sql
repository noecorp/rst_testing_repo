
ALTER TABLE `rat_corp_load_request`
ADD COLUMN `txn_type`  char(4) NOT NULL AFTER `customer_purse_id`;

ALTER TABLE `card_txn_processing`
ADD COLUMN `txn_type`  char(4) NOT NULL AFTER `card_number`;

SET @flag_id := (SELECT id FROM `t_flags` WHERE name = 'operation-corp_ratnakar_cardholder');

SET @priv_id := (SELECT id FROM `t_privileges` WHERE `flag_id` = @flag_id AND name ='index');
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, '3', @flag_id, @priv_id, '1');

SET @priv_id := (SELECT id FROM `t_privileges` WHERE `flag_id` = @flag_id AND name ='uploadcardholders');
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, '3', @flag_id, @priv_id, '1');

SET @priv_id := (SELECT id FROM `t_privileges` WHERE `flag_id` = @flag_id AND name ='searchcardholder');
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, '3', @flag_id, @priv_id, '1');

SET @priv_id := (SELECT id FROM `t_privileges` WHERE `flag_id` = @flag_id AND name ='view');
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, '3', @flag_id, @priv_id, '1');

SET @priv_id := (SELECT id FROM `t_privileges` WHERE `flag_id` = @flag_id AND name ='addcardholderdocs');
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, '3', @flag_id, @priv_id, '1');



SET @flag_id := (SELECT id FROM `t_flags` WHERE name = 'operation-corp_ratnakar_cardload');

SET @priv_id := (SELECT id FROM `t_privileges` WHERE `flag_id` = @flag_id AND name ='corporateload');
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, '3', @flag_id, @priv_id, '1');

SET @priv_id := (SELECT id FROM `t_privileges` WHERE `flag_id` = @flag_id AND name ='walletstatus');
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, '3', @flag_id, @priv_id, '1');

SET @flag_id := (SELECT id FROM `t_flags` WHERE name='operation-reports');
SET @priv_id := (SELECT id FROM `t_privileges` WHERE `flag_id` = @flag_id AND name ='wallettxn');
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, '3', @flag_id, @priv_id, '1');
