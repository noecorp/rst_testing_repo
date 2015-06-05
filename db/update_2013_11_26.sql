SET @product_id := (SELECT id FROM `t_products` WHERE `unicode` = 510 AND status ='active');
INSERT INTO `t_product_privileges` (`product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (@product_id, '70', '355', 1);
INSERT INTO `t_product_privileges` (`product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (@product_id, '70', '356', 1);
INSERT INTO `t_product_privileges` (`product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (@product_id, '70', '368', 1);
INSERT INTO `t_product_privileges` (`product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (@product_id, '70', '369', 1);


SET @product_id := (SELECT id FROM `t_products` WHERE `unicode` = 510 AND status ='active');

INSERT INTO `t_flags` (`name`, `description`, `active_on_dev`, `active_on_prod`) VALUES ('agent-corp_kotak_customer', 'Kotak Amul Cardholders', '1', '0');
SET @flag_id_val = last_insert_id();

Insert into `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'adddetails', @flag_id_val, 'Add Customer Details');
SET @privilege_id = last_insert_id();

Insert into `t_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @product_id, @flag_id_val, @privilege_id, '1');

ALTER TABLE `kotak_corp_cardholders` ADD `state` VARCHAR( 50 ) NOT NULL AFTER `city`;

ALTER TABLE `kotak_corp_cardholders` ADD `comm_state` VARCHAR( 50 ) NOT NULL AFTER `comm_pin`;

ALTER TABLE `kotak_corp_cardholders` DROP `telephone`;



CREATE TABLE IF NOT EXISTS `t_afn_no` (
  `afn_no` int(11) unsigned NOT NULL,
  `status` enum('free','used','block') DEFAULT 'free',
  `date_added` datetime DEFAULT NULL,
  UNIQUE KEY `afnno` (`afn_no`)
) ENGINE=InnoDB;

ALTER TABLE `t_docs` CHANGE `doc_type` `doc_type` ENUM( 'pan', 'passport', 'others', 'photo', 'shop photo', 'id proof', 'address proof', 'electricity bill', 'telephone bill', 'state govt letter', 'ration card', 'id card', 'uid', 'voter id', 'driving licence', 'marriage certificate' ) NOT NULL;
ALTER TABLE `kotak_corp_cardholders` CHANGE `farmer_id` `member_id` VARCHAR( 10 ) NOT NULL;

ALTER TABLE `t_docs` ADD `doc_kotak_amul_id` INT( 11 ) UNSIGNED NOT NULL AFTER `doc_rat_corp_id`;