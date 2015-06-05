INSERT INTO `t_cron` (`id`, `name`, `description`, `file_name`, `status`, `status_cron`, `date_updated`) VALUES (60, 'Remittance Transaction Recon Report', 'Remittance Transaction Recon Report', 'RemittanceTransactionRecon.php', 'active', 'completed', NOW());

SET @group_id := 3;
SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-remit_reports'); 
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'remitrecon', @flag_id, 'Remittance Transaction Recon  Report');
SET @priv_id = last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @group_id, @flag_id, @priv_id, '1');

