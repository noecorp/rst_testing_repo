
SET @flg_id := (SELECT id FROM `t_flags` where name ='operation-corp_boi_customer');

DELETE FROM t_privileges WHERE flag_id = @flg_id;

DELETE from t_flags WHERE name = 'operation-corp_boi_customer' AND id = @flg_id;

SET @ops_id = '3';
INSERT INTO `t_flags` (`name`, `description`, `active_on_dev`, `active_on_prod`) VALUES ('operation-corp_boi_customer', 'BOI NSDC Customer', '1', '0');
SET @flag_id = last_insert_id();

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'customerlist', @flag_id, 'BOI NSDC pending Customer list'); 
SET @priv_id = last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'search', @flag_id, 'BOI NSDC Customer search Page'); 
SET @priv_id = last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'bankstatus', @flag_id, 'BOI NSDC Customer bank status Page'); 
SET @priv_id = last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'approve', @flag_id, 'BOI NSDC Customer approval page'); 
SET @priv_id = last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'reject', @flag_id, 'BOI NSDC Customer rejection page'); 
SET @priv_id = last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');


INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'view', @flag_id, 'BOI NSDC Customer detail page'); 
SET @priv_id = last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');


INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'resubmit', @flag_id, 'BOI NSDC Customer resubmit for approval'); 
SET @priv_id = last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

ALTER TABLE `t_agents` ADD `centre_id` VARCHAR( 50 ) NOT NULL AFTER `agent_code` ,
ADD `terminal_id_tid_1` VARCHAR( 50 ) NOT NULL AFTER `centre_id` ,
ADD `terminal_id_tid_2` VARCHAR( 50 ) NOT NULL AFTER `terminal_id_tid_1` ,
ADD `terminal_id_tid_3` VARCHAR( 50 ) NOT NULL AFTER `terminal_id_tid_2`;

ALTER TABLE `boi_corp_cardholders` CHANGE `country_id` `country_code` VARCHAR( 5 ) NOT NULL ,
CHANGE `comm_country_id` `comm_country_code` VARCHAR( 5 ) NOT NULL ;

ALTER TABLE `t_products`
ADD COLUMN `const`  varchar(30) NULL AFTER `unicode`;

UPDATE `t_products` SET `const`='BOI_NSDC' WHERE (`id`='7');
UPDATE `t_products` SET `const`='RATNAKAR_MEDIASSIST' WHERE (`id`='3');
ALTER TABLE `t_agents` ADD `centre_id` VARCHAR( 50 ) NOT NULL AFTER `agent_code` ,
ADD `terminal_id_tid_1` VARCHAR( 50 ) NOT NULL AFTER `centre_id` ,
ADD `terminal_id_tid_2` VARCHAR( 50 ) NOT NULL AFTER `terminal_id_tid_1` ,
ADD `terminal_id_tid_3` VARCHAR( 50 ) NOT NULL AFTER `terminal_id_tid_2`;
