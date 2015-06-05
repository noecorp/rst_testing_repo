ALTER TABLE `rat_corp_cardholders`
MODIFY COLUMN `gender`  enum('male','female')  NOT NULL DEFAULT 'male' AFTER `name_on_card`;

ALTER TABLE `rat_corp_cardholders`
ADD COLUMN `rat_customer_id`  int(11) UNSIGNED NOT NULL AFTER `customer_master_id`;

SET @flag_id := (SELECT id FROM `t_flags` WHERE name = 'operation-product');
INSERT INTO `t_privileges` (`id` ,`name` ,`flag_id` ,`description`)
VALUES (NULL , 'view', @flag_id, 'View product details');