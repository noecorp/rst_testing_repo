SET @flag_id := (SELECT id FROM `t_flags` WHERE name='operation-remit_boi_remitter');
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'neftbatchdetails', @flag_id, 'NEFT Batch Details');
SET @flag_id := (SELECT id FROM `t_flags` WHERE name='operation-remit_reports');
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'neftresponse', @flag_id, 'NEFT Response Details');
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'exportneftresponse', @flag_id, 'Export NEFT Response Details');