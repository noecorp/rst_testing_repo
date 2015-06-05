SET @product_id := (select id from t_products where name='MEDI ASSIST CARD');
SET @flag_id := (SELECT id FROM t_flags WHERE name = 'agent-corp_ratnakar_cardload');
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'checkstatus', @flag_id, 'Check Card Load Status');
SET @privilege_id = last_insert_id();
Insert into `t_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @product_id, @flag_id, @privilege_id, '1');
ALTER TABLE `rat_corp_cardholders` CHANGE `status` `status` ENUM( 'pending', 'active', 'inactive', 'incomplete', 'failed', 'pending_reg' ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT 'pending';
ALTER TABLE `rat_corp_cardholders` ADD `failed_reason` VARCHAR( 200 ) NOT NULL AFTER `date_updated` ,
ADD `date_failed` TIMESTAMP NOT NULL AFTER `failed_reason`;
SET @flag_id := (SELECT id FROM t_flags WHERE name = 'operation-history');
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'cron', @flag_id, 'See Cron Logs');
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'cron', @flag_id, 'See Cron Logs');