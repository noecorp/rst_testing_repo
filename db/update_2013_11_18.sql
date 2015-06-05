INSERT INTO `t_transaction_type` (`typecode`, `name`) VALUES ('CRMA', 'Credit Manual Adjustment');
INSERT INTO `t_transaction_type` (`typecode`, `name`) VALUES ('DRMA', 'Debit Manual Adjustment');

ALTER TABLE `t_batch_adjustment`
ADD COLUMN `date_failed`  datetime NULL AFTER `date_created`;

ALTER TABLE `t_batch_adjustment`
MODIFY COLUMN `status`  enum('failed','success','duplicate','pending','in_process') NOT NULL DEFAULT 'pending' AFTER `rrn`;

ALTER TABLE `t_batch_adjustment`
ADD COLUMN `date_updated`  timestamp NULL ON UPDATE CURRENT_TIMESTAMP AFTER `date_failed`;

INSERT INTO `t_cron` (`id`, `name`, `description`, `file_name`, `status`) VALUES ('20', 'Manual Adjustment', 'Ratnakar Manual Adjustment', 'RatManualAdjustment.php', 'active');

