TRUNCATE TABLE t_agent_closing_balance;

SET @flag_id := (SELECT id FROM `t_flags` WHERE name='operation-reports');
Insert into `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'agentcommissionsummary', @flag_id, 'Agent Commission Summary Report');
Insert into `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'exportagentcommissionsummary', @flag_id, 'Export Agent Commission Summary Report');