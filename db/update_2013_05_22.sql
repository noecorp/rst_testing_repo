SET @flag_id := (SELECT id FROM `t_flags` WHERE name='operation-remit_reports');
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'remittancerefund', @flag_id, 'Remittance Refunds Report');
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'exportremittancerefund', @flag_id, 'Export Remittance Refunds Report');