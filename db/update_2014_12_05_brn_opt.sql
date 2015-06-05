ALTER TABLE `t_files`  ADD `params` VARCHAR(255) NOT NULL AFTER `file_name`;

ALTER TABLE  `t_files` CHANGE  `status`  `status` ENUM(  'active',  'inactive',  'pending',  'started' ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT  'active';

ALTER TABLE `t_files`  ADD `bank_id` INT(11) UNSIGNED NOT NULL AFTER `id`;

INSERT INTO `t_cron` (`id`, `name`, `description`, `file_name`, `status`, `status_cron`, `date_updated`) VALUES (51, 'Generate Remittance Transaction Report File', 'Cron will generate remittance transaction report file', 'GenerateRemittanceTransactionFile.php', 'active', 'completed', CURRENT_TIMESTAMP);
