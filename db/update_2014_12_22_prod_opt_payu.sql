INSERT INTO `t_cron` (`id`, `name`, `description`, `file_name`, `status`, `status_cron`, `date_updated`) VALUES (80, 'Agent Funding & Corporate Funding', 'Agent Funding & Corporate Funding against bank statements.', 'PartnerFunding.php', 'active', 'completed', NOW());

UPDATE t_cron SET status = 'inactive', status_cron = 'stopped' WHERE id = 18 LIMIT 1;