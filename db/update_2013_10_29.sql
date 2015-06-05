SET @flag_id := (SELECT id FROM `t_flags` WHERE name = 'operation-remit_kotak_remitter');

SET @priv_id := (SELECT id FROM `t_privileges` WHERE `flag_id` = @flag_id AND name ='search');
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, '3', @flag_id, @priv_id, '1');

SET @priv_id := (SELECT id FROM `t_privileges` WHERE `flag_id` = @flag_id AND name ='holdtransactions');
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, '3', @flag_id, @priv_id, '1');

SET @priv_id := (SELECT id FROM `t_privileges` WHERE `flag_id` = @flag_id AND name ='processtransaction');
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, '3', @flag_id, @priv_id, '1');

SET @priv_id := (SELECT id FROM `t_privileges` WHERE `flag_id` = @flag_id AND name ='checkstatus');
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, '3', @flag_id, @priv_id, '1');

SET @priv_id := (SELECT id FROM `t_privileges` WHERE `flag_id` = @flag_id AND name ='index');
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, '3', @flag_id, @priv_id, '1');

SET @priv_id := (SELECT id FROM `t_privileges` WHERE `flag_id` = @flag_id AND name ='beneficiary');
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, '3', @flag_id, @priv_id, '1');


SET @flag_id := (SELECT id FROM `t_flags` WHERE name = 'operation-agentfunding');

SET @priv_id := (SELECT id FROM `t_privileges` WHERE `flag_id` = @flag_id AND name ='index');
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, '3', @flag_id, @priv_id, '1');

SET @priv_id := (SELECT id FROM `t_privileges` WHERE `flag_id` = @flag_id AND name ='uploadbankstatement');
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, '3', @flag_id, @priv_id, '1');

SET @priv_id := (SELECT id FROM `t_privileges` WHERE `flag_id` = @flag_id AND name ='afteruploadbankstatement');
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, '3', @flag_id, @priv_id, '1');

SET @priv_id := (SELECT id FROM `t_privileges` WHERE `flag_id` = @flag_id AND name ='pendingfundrequest');
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, '3', @flag_id, @priv_id, '1');

SET @priv_id := (SELECT id FROM `t_privileges` WHERE `flag_id` = @flag_id AND name ='confirmbeforesettlement');
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, '3', @flag_id, @priv_id, '1');

SET @priv_id := (SELECT id FROM `t_privileges` WHERE `flag_id` = @flag_id AND name ='confirmsettlement');
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, '3', @flag_id, @priv_id, '1');

SET @priv_id := (SELECT id FROM `t_privileges` WHERE `flag_id` = @flag_id AND name ='dosettlement');
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, '3', @flag_id, @priv_id, '1');

SET @priv_id := (SELECT id FROM `t_privileges` WHERE `flag_id` = @flag_id AND name ='confirmbeforerejectfundrequest');
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, '3', @flag_id, @priv_id, '1');

SET @priv_id := (SELECT id FROM `t_privileges` WHERE `flag_id` = @flag_id AND name ='rejectfundrequest');
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, '3', @flag_id, @priv_id, '1');

SET @priv_id := (SELECT id FROM `t_privileges` WHERE `flag_id` = @flag_id AND name ='unsettledbankstatement');
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, '3', @flag_id, @priv_id, '1');

SET @priv_id := (SELECT id FROM `t_privileges` WHERE `flag_id` = @flag_id AND name ='settledfundrequest');
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, '3', @flag_id, @priv_id, '1');

SET @priv_id := (SELECT id FROM `t_privileges` WHERE `flag_id` = @flag_id AND name ='exportpendingfundrequest');
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, '3', @flag_id, @priv_id, '1');

SET @priv_id := (SELECT id FROM `t_privileges` WHERE `flag_id` = @flag_id AND name ='exportunsettledbankstatement');
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, '3', @flag_id, @priv_id, '1');

SET @priv_id := (SELECT id FROM `t_privileges` WHERE `flag_id` = @flag_id AND name ='exportsettledfundrequest');
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, '3', @flag_id, @priv_id, '1');

ALTER TABLE `rat_corp_load_request`
ADD COLUMN `txn_load_id`  int(11) UNSIGNED NOT NULL AFTER `date_failed`;

INSERT INTO t_transaction_type (typecode, name, status, is_comm) VALUES ('RRCC', 'Reversal Ratnakar Corporate CardLoad', 'active', 'no');
INSERT INTO t_transaction_type (typecode, name, status, is_comm) VALUES ('RRCM', 'Reversal Ratnakar Medi-Assist CardLoad', 'active', 'no');


ALTER TABLE `rat_corp_load_request`
ADD COLUMN `date_cutoff`  datetime NULL AFTER `date_failed`;

