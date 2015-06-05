SET @flag_id := (SELECT id FROM `t_flags` WHERE name='operation-remit_reports');
Insert into `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'remittertransaction', @flag_id, 'Remitter Transactions Report');
Insert into `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'exportremittertransaction', @flag_id, 'Export Remitter Transactions Report');