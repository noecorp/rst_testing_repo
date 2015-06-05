SET @flag_id := (SELECT id FROM `t_flags` WHERE name='operation-hic_ratnakar_cardholder');
UPDATE `t_privileges` SET name = 'uploadcardholders' WHERE name = 'uploadcardholder' AND flag_id = @flag_id;
-- Missed to add customer_master_id added twice in detail page - FIXED
ALTER TABLE `rat_hic_cardholders` ADD `customer_master_id` INT( 11 ) NOT NULL AFTER `id`;
ALTER TABLE `rat_hic_cardholders` ADD `product_id` INT( 11 ) NOT NULL AFTER `customer_master_id`;
ALTER TABLE `rat_hic_cardholder_details` ADD `product_id` INT( 11 ) NOT NULL AFTER `id`;


ALTER TABLE `rat_hic_cardholders` ADD `upload_status` ENUM( 'temp', 'pass', 'duplicate' ) NOT NULL DEFAULT 'temp' AFTER `date_updated`;

ALTER TABLE `rat_hic_cardholder_details` ADD `customer_master_id` INT( 11 ) NOT NULL AFTER `id`;

ALTER TABLE  `rat_hic_cardholders` ADD  `by_agent_id` INT( 11 ) UNSIGNED NOT NULL AFTER  `by_ops_id`;




INSERT INTO `t_flags` (`name`, `description`, `active_on_dev`, `active_on_prod`) VALUES ('agent-hic_ratnakar_cardholder', 'Cardholder of ratnakar bank hic', '1', '0');
SET @flag_id = last_insert_id();
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'add', @flag_id, 'Add Cardholder');
SET @privilege_id = last_insert_id();
SET @product_id := (SELECT id from t_products where name='MEDI ASSIST CARD');
INSERT INTO `t_product_privileges` (`id`, `product_id`, `flag_id`, privilege_id, `allow`) VALUES (NULL, @product_id, @flag_id, @privilege_id, 1);

