SET @flag_id := (SELECT id FROM `t_flags` WHERE name='operation-reports');
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'exportfeereport', @flag_id, 'Export fee Report');
SET @flag_id := (SELECT id FROM `t_flags` WHERE name='operation-mvc_axis_reports');
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'loadreloadcomm', @flag_id, 'Load Reload commission Report');
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'exportloadreloadcomm', @flag_id, 'Export Load Reload commission Report');
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'agentwiseloadreloadcomm', @flag_id, 'Agent Wise Load Reload commission Report');
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'exportagentwiseloadreloadcomm', @flag_id, 'Export Agent Wise Load Reload commission Report');

SET @flag_id := (SELECT id FROM `t_flags` WHERE name='agent-mvc_axis_reports');
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'loadreloadcomm', @flag_id, 'Load Reload commission Report');
SET @privilege_id = last_insert_id();
INSERT INTO `t_product_privileges` (`id`, `product_id`, `flag_id`, privilege_id, `allow`) VALUES (NULL, 1, @flag_id, @privilege_id, 1);
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'exportloadreloadcomm', @flag_id, 'Export Load Reload commission Report');
SET @privilege_id = last_insert_id();
INSERT INTO `t_product_privileges` (`id`, `product_id`, `flag_id`, privilege_id, `allow`) VALUES (NULL, 1, @flag_id, @privilege_id, 1);


ALTER TABLE `t_remittance_request`
MODIFY COLUMN `status_sms`  enum('pending','success','failure') CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT 'pending' AFTER `neft_remarks`;

ALTER TABLE `t_remittance_request`
ADD COLUMN `neft_processed`  enum('yes','no') NOT NULL DEFAULT 'no' AFTER `batch_name`;