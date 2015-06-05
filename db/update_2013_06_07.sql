SET @flag_id := (SELECT id FROM `t_flags` WHERE name='operation-remit_reports');
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'remittanceexception', @flag_id, 'Remittance Exception');
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'exportremittanceexception', @flag_id, 'Export Remittance Exception');