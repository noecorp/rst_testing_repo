SET @flag_id := (SELECT id FROM `t_flags` WHERE name='operation-logs');
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'bank', @flag_id, 'Bank Logs');

SET @flag_id := (SELECT id FROM `t_flags` WHERE name='operation-remit_boi_remitter');
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'neftresponse', @flag_id, 'Neft Response');
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'neftupdate', @flag_id, 'Neft Response Processing');

ALTER TABLE `t_remittance_request` ADD COLUMN `neft_remarks`  varchar(250) NULL AFTER `batch_name`;

ALTER TABLE `t_remittance_request` ADD COLUMN `status_sms`  enum('pending','success','failure') NULL DEFAULT 'pending' AFTER `neft_remarks`;

UPDATE `t_cron` SET `name` = 'NEFT Response Send SMS',
`description` = 'Cron will send remittance neft response (success/failure) SMS',
`file_name` = 'NEFTResponseSendSms.php' WHERE `t_cron`.`id` =13 LIMIT 1;

