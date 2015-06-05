ALTER TABLE `boi_corp_cardholders` ADD `aof_ref_num` VARCHAR( 50 ) NOT NULL AFTER `ref_num`;
ALTER TABLE `boi_corp_cardholders_details` ADD `aof_ref_num` VARCHAR( 50 ) NOT NULL AFTER `ref_num`;

ALTER TABLE `kotak_corp_load_request`
MODIFY COLUMN `status`  enum('pending','loaded','failed','cutoff','blocked','completed','incomplete','started') NOT NULL DEFAULT 'pending' AFTER `date_updated`;

DELETE FROM `t_product_privileges` WHERE `product_id` =7 AND `flag_id` =64;

ALTER TABLE `rat_corp_load_request`
MODIFY COLUMN `status`  enum('pending','loaded','failed','cutoff','blocked','completed','incomplete','started')  NOT NULL DEFAULT 'pending' AFTER `date_updated`;


ALTER TABLE `boi_corp_load_request`
MODIFY COLUMN `status`  enum('pending','loaded','failed','cutoff','blocked','completed','incomplete','started') NOT NULL DEFAULT 'pending' AFTER `date_updated`;


DELETE FROM `t_product_privileges` WHERE `product_id` =7 AND `flag_id` = 42;