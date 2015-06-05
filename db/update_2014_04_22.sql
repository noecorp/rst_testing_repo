ALTER TABLE `rat_customer_purse`
ADD COLUMN `customer_master_id`  int UNSIGNED NOT NULL AFTER `id`;

ALTER TABLE `kotak_customer_purse`
ADD COLUMN `customer_master_id`  int UNSIGNED NOT NULL AFTER `id`;

ALTER TABLE `boi_customer_purse`
ADD COLUMN `customer_master_id`  int(11) UNSIGNED NOT NULL AFTER `id`;


SET @product_id := (select id from t_products where unicode = '810' LIMIT 1);
SET @agent_id := '332';-- AS DEFINED IN CONSTANTS

ALTER TABLE `rat_corp_cardholders` ADD `by_agent_id` INT( 11 ) UNSIGNED NOT NULL AFTER `by_ops_id` ;

UPDATE `rat_corp_cardholders` SET `by_agent_id` = @agent_id WHERE `product_id` = @product_id;
UPDATE `rat_corp_load_request` SET `by_agent_id` = @agent_id WHERE `product_id` = @product_id;


SET @flag_id := (select id from t_flags where name='operation-settings' LIMIT 1);
SET @ops_id = '3';

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'addagentcity', @flag_id, 'Add city for agent signup');
SET @priv_id = last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');



INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'addcustomercity', @flag_id, 'Add city for NSDC customer signup');
SET @priv_id = last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');


