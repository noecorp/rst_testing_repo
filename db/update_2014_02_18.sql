ALTER TABLE `boi_corp_cardholders`
ADD COLUMN `boi_card_mapping_id`  int(11) UNSIGNED NULL AFTER `date_approval`;

ALTER TABLE `boi_card_mapping`
ADD COLUMN `date_failed`  datetime NULL AFTER `failed_reason`;

INSERT INTO `t_cron` (`id`, `name`, `description`, `file_name`, `status`, `status_cron`, `date_updated`) VALUES ('27', 'BOI NSDC Account mapping', 'BOI NSDC Account mapping', '710Mapping.php', 'active', 'completed', CURRENT_TIMESTAMP);

SET @flg_id := (SELECT id FROM `t_flags` where name ='operation-corp_boi_customer');
Insert into `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'cuttofffile', @flg_id, 'TTUM File Generation');
SET @privilege_id = last_insert_id();

Insert into `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, '3', @flg_id, @privilege_id, '1');

ALTER TABLE `boi_corp_cardholders` ADD `boi_account_number` VARCHAR( 20 ) NOT NULL AFTER `account_type_id`;