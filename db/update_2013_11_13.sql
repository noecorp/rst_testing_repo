SET @ops_id := 3;

SET @flag_id := (SELECT id FROM `t_flags` WHERE name = 'operation-corp_ratnakar_cardholder');

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'uploadma', @flag_id, 'Upload Manual Adjustment');
SET @priv_id := (SELECT LAST_INSERT_ID());
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

SET @priv_id := (SELECT LAST_INSERT_ID());
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'searchma', @flag_id, 'Search Manual Adjustment');
SET @priv_id := (SELECT id FROM `t_privileges` WHERE `flag_id` = @flag_id AND name ='batchstatus');
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

ALTER TABLE `rat_corp_cardholders`
ADD COLUMN `batch_id`  int(11) UNSIGNED NOT NULL AFTER `by_ops_id`;
