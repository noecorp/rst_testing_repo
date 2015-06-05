SET @flg_id := (SELECT id FROM `t_flags` where name ='operation-corp_boi_customer');


Insert into `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'cardmapping', @flg_id, 'CRN Status Report');
SET @privilege_id = last_insert_id();

Insert into `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, '3', @flg_id, @privilege_id, '1');

Insert into `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'cardmappingstatus', @flg_id, 'CRN Status Report');
SET @privilege_id = last_insert_id();

Insert into `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, '3', @flg_id, @privilege_id, '1');


CREATE TABLE IF NOT EXISTS `boi_card_mapping` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) unsigned NOT NULL,
  `card_number` varchar(16) NOT NULL,
  `card_pack_id` varchar(20) NOT NULL,
  `boi_account_number` varchar(20) NOT NULL,
  `boi_customer_id` varchar(20) NOT NULL,
  `batch_name` varchar(100) NOT NULL,
  `date_ecs` datetime DEFAULT NULL,
  `failed_reason` varchar(100) DEFAULT NULL,
  `status` enum('pending','success','failure') NOT NULL DEFAULT 'pending',
  PRIMARY KEY (`id`)
) AUTO_INCREMENT=1 ;

ALTER TABLE `boi_card_mapping` ADD `date_created` DATETIME NOT NULL AFTER `batch_name`;