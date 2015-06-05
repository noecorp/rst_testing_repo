SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-corp_boi_customer');

SET @ops_id = '3';

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'accountload', @flag_id, 'BOI NSDC account load'); 
SET @priv_id = last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

INSERT INTO `t_cron` (`id`, `name`, `description`, `file_name`, `status`, `status_cron`, `date_updated`) VALUES ('28', 'BOI NSDC Account load', 'BOI NSDC Account load', 'BoiAccountLoad.php', 'active', 'completed', CURRENT_TIMESTAMP);

ALTER TABLE `boi_corp_load_request_batch`
ADD COLUMN `type`  enum('wlt','act') NULL DEFAULT NULL AFTER `product_id`;

UPDATE boi_corp_load_request_batch SET type = 'wlt' ;

ALTER TABLE `boi_corp_load_request`
ADD COLUMN `type`  enum('wlt','act') NULL DEFAULT NULL AFTER `product_id`;

UPDATE boi_corp_load_request SET type = 'wlt' ;

ALTER TABLE `boi_delivery_file_master`
MODIFY COLUMN `member_id`  varchar(20) NOT NULL AFTER `boi_customer_id`;

ALTER TABLE `boi_corp_cardholders`
MODIFY COLUMN `member_id`  varchar(20) NOT NULL AFTER `afn`;

ALTER TABLE `boi_corp_load_request`
MODIFY COLUMN `member_id`  varchar(20) NOT NULL AFTER `card_number`;

ALTER TABLE `boi_corp_load_request_batch`
MODIFY COLUMN `member_id`  varchar(20)  NOT NULL AFTER `card_number`;

