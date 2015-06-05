INSERT INTO `t_cron` (`id`, `name`, `description`, `file_name`, `status`, `status_cron`, `date_updated`) VALUES ('14', 'NEFT Batch Creation', 'Cron will create the batch for neft', 'NeftBatchCreation.php', 'active', 'completed', CURRENT_TIMESTAMP);
UPDATE t_cron SET file_name = 'NEFTBatchCreation.php' WHERE id = 14 LIMIT 1;
SET @flag_id := (SELECT id FROM `t_flags` WHERE name='operation-reports');
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'pendingagentfundrequests', @flag_id, 'Pending Agent Fund Request Details');
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'exportpendingagentfundrequests', @flag_id, 'Export Pending Agent Fund Request Details');