SET @flag_id := (SELECT id FROM `t_flags` where name ='bank-corp_kotak_reports');

SET @ops_id = '3';

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'remitterregn', @flag_id, 'Remitter Registration Report');
SET @priv_id = last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'exportremitterregn', @flag_id, 'Export Remitter Registration Report');
SET @priv_id = last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');


SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-reports');

SET @ops_id = '3';

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'agentremittance', @flag_id, 'Agent Remittance Reports');
SET @priv_id = last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'exportagentremittance', @flag_id, 'Export Agent Remittance Reports');
SET @priv_id = last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');


ALTER TABLE `bank_statement`  ADD `bank_id` INT(11) NOT NULL AFTER `balance`;

SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-corporatelimit'); 

SET @ops_id = '3';

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'addlimit', @flag_id, 'Assign Corporate Limit');
SET @priv_id = last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'editlimit', @flag_id, 'Edit Assigned Corporate Limits');
SET @priv_id = last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'deletelimit', @flag_id, 'Delete Assigned Corporate Limits');
SET @priv_id = last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'editcorplimit', @flag_id, 'Edit Corporate Limits');
SET @priv_id = last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'deletecorplimit', @flag_id, 'Delete Corporate Limits');
SET @priv_id = last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');


ALTER TABLE `corporate_bind_product_commission` DROP `corporate_limit_id` ;

