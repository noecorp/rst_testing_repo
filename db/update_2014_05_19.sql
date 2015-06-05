ALTER TABLE `boi_corp_cardholders_details`
ADD COLUMN `product_customer_id`  int(11) UNSIGNED NOT NULL AFTER `id`;
SET @flag_id := (SELECT id FROM `t_flags` WHERE name='operation-corp_boi_reports');

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'rbi', @flag_id, 'BOI NSDC RBI Reporting');
SET @priv_id = last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, '3', @flag_id, @priv_id, '1');


INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'exportrbi', @flag_id, 'Export BOI NSDC RBI Reporting');
SET @priv_id = last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, '3', @flag_id, @priv_id, '1');



