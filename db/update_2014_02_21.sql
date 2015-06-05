SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-corp_boi_customer');

SET @ops_id = '3';
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'walletstatus', @flag_id, 'BOI NSDC wallet status'); 
SET @priv_id = last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'exportwalletstatus', @flag_id, 'Export BOI NSDC wallet status'); 
SET @priv_id = last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

ALTER TABLE `t_products` ADD `static_otp` ENUM( 'yes', 'no' ) NOT NULL DEFAULT 'no' AFTER `flag_common`;

UPDATE `t_products` SET `static_otp` = 'yes' WHERE `unicode` = '410';

ALTER TABLE `kotak_remit_remitters` ADD `static_code` VARCHAR( 40 ) NOT NULL AFTER `txn_code`;


SET @product_id := (select id from t_products where unicode = '410' LIMIT 1);
SET @flag_id := (select id from t_flags where name='agent-remit_kotak_beneficiary' LIMIT 1);


INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'transferfundstaticcode', @flag_id, 'Fund Transfer with Static Code');
SET @privilege_id = last_insert_id();
Insert into `t_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @product_id, @flag_id, @privilege_id, '1');
