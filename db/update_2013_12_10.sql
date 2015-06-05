SET @ops_id := 3;

SET @flag_id := (SELECT id FROM `t_flags` WHERE name = 'operation-corp_kotak_customer');

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'deliverystatus', @flag_id, 'Status report of delivery file');
SET @priv_id := (SELECT LAST_INSERT_ID());
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');


ALTER TABLE `delivery_file_master`
ADD COLUMN `product_id`  int(11) UNSIGNED NOT NULL AFTER `id`,
ADD COLUMN `date_ecs`  datetime NULL AFTER `batch_name`,
ADD COLUMN `failed_reason`  varchar(100) NULL AFTER `date_ecs`,
ADD COLUMN `status`  enum('pending','success','failure') NOT NULL DEFAULT 'pending' AFTER `failed_reason`;

ALTER TABLE `kotak_corp_cardholders`
ADD COLUMN `delivery_file_id`  int(11) UNSIGNED NULL AFTER `date_updated`;