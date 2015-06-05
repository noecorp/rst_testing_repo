SET @flag_id := (SELECT id FROM `t_flags` WHERE name='operation-remit_reports');
Insert into `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'remittancecommission', @flag_id, 'Remittance Commission Report');
Insert into `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'exportremittancecommission', @flag_id, 'Export Remittance Commission Report');
Insert into `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'agentwiseremittancecommission', @flag_id, 'Agent wise Remittance Commission Report');
Insert into `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'exportagentwiseremittancecommission', @flag_id, 'Export Agent wise Remittance Commission Report');

