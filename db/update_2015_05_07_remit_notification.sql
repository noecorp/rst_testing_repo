INSERT INTO `t_cron` (`id`, `name`, `description`, `file_name`, `status`, `status_cron`, `date_updated`) VALUES (91, 'Remittance Notification', 'Notification for Remittance after Bank Confirmation', 'RatRemittanceNotification.php', 'active', 'completed', NOW());

ALTER TABLE `rat_remittance_request` ADD `flag_response` ENUM( '0', '1', '2' ) NOT NULL DEFAULT '0' AFTER `status_response_by_ops_id`;
