SET @flag_id := (SELECT id FROM `t_flags` WHERE name='operation-agentfunding');

INSERT INTO `t_privileges` (`id` ,`name` ,`flag_id` ,`description`)
VALUES (NULL , 'exportpendingfundrequest', @flag_id, 'Export pending fund request');

INSERT INTO `t_privileges` (`id` ,`name` ,`flag_id` ,`description`)
VALUES (NULL , 'exportunsettledbankstatement', @flag_id, 'Export unsettled bank statement');

INSERT INTO `t_privileges` (`id` ,`name` ,`flag_id` ,`description`)
VALUES (NULL , 'exportsettledfundrequest', @flag_id, 'Export settled fund request');

