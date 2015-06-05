SET @flag_id := (SELECT id FROM `t_flags` WHERE name='operation-remit_reports');
Insert into `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'remittancereport', @flag_id, 'Remittance Report');
Insert into `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'exportremittancereport', @flag_id, 'Export Remittance Report');
Insert into `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'agentwiseremittancereport', @flag_id, 'Agent wise Remittance Report');
Insert into `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'exportagentwiseremittancereport', @flag_id, 'Export Agent wise Remittance Report');

