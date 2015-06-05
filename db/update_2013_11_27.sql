ALTER TABLE `kotak_corp_cardholders` CHANGE `status` `status` ENUM( 'active', 'inactive', 'pending' ) NOT NULL DEFAULT 'pending';

ALTER TABLE `kotak_corp_cardholders` ADD `status_bank` ENUM( 'pending', 'active', 'inactive' ) NOT NULL DEFAULT 'pending' AFTER `status` ,
ADD `status_ops` ENUM( 'pending', 'active', 'inactive' ) NOT NULL DEFAULT 'pending' AFTER `status_bank` ,
ADD `status_ecs` ENUM( 'pending', 'active', 'inactive' ) NOT NULL DEFAULT 'pending' AFTER `status_ops`;

INSERT INTO `t_flags` (`name`, `description`, `active_on_dev`, `active_on_prod`) VALUES ('operation-corp_kotak_customer', 'Kotak Amul Cardholders', '1', '0');
SET @flag_id_val = last_insert_id();

Insert into `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'index', @flag_id_val, 'Kotak Amul Index page');
SET @priv_id_val = last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, '3', @flag_id_val, @priv_id_val, '1');

Insert into `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'search', @flag_id_val, 'Search Customer');
SET @priv_id_val = last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, '3', @flag_id_val, @priv_id_val, '1');

Insert into `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'approve', @flag_id_val, 'Approve Customer');
SET @priv_id_val = last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, '3', @flag_id_val, @priv_id_val, '1');

Insert into `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'reject', @flag_id_val, 'Reject Customer');
SET @priv_id_val = last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, '3', @flag_id_val, @priv_id_val, '1');

CREATE TABLE `kotak_corp_log_cardholder` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `kotak_customer_id` int(11) unsigned NOT NULL,
  `product_customer_id` int(11) unsigned NOT NULL,
  `status_old` varchar(15) DEFAULT NULL,
  `status_new` varchar(15) DEFAULT NULL,
  `status_ops_old` varchar(15) DEFAULT NULL,
  `status_ops_new` varchar(15) DEFAULT NULL,
  `status_bank_old` varchar(15) DEFAULT NULL,
  `status_bank_new` varchar(15) DEFAULT NULL,
  `status_ecs_old` varchar(15) DEFAULT NULL,
  `status_ecs_new` varchar(15) DEFAULT NULL,
  `comments` tinytext,
  `by_type` enum('maker','checker','authorizer','ecs','system') NOT NULL,
  `by_id` int(11) unsigned NOT NULL,
  `ip` varchar(15) DEFAULT NULL,
  `date_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;