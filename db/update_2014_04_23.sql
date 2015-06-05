SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `t_bank_groups`;
CREATE TABLE `t_bank_groups` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(25) DEFAULT NULL,
  `parent_id` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_name` (`name`) USING BTREE,
  KEY `idx_parent_id` (`parent_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


INSERT INTO `t_bank_groups` (`name`) VALUES ('KOTAK_BANK')

SET @group_id = last_insert_id();

DROP TABLE IF EXISTS `t_bank_users_groups`;
CREATE TABLE `t_bank_users_groups` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `group_id` int(11) unsigned NOT NULL,
  `user_id` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_bank_group` (`group_id`,`user_id`) USING BTREE,
  KEY `idx_user_id` (`user_id`) USING BTREE,
  KEY `idx_group_id` (`group_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


INSERT INTO `t_bank_users_groups` (`group_id`, `user_id`) VALUES (@group_id, '1');
INSERT INTO `t_bank_users_groups` (`group_id`, `user_id`) VALUES (@group_id, '2');
INSERT INTO `t_bank_users_groups` (`group_id`, `user_id`) VALUES (@group_id, '3');
INSERT INTO `t_bank_users_groups` (`group_id`, `user_id`) VALUES (@group_id, '4');
INSERT INTO `t_bank_users_groups` (`group_id`, `user_id`) VALUES (@group_id, '5');
INSERT INTO `t_bank_users_groups` (`group_id`, `user_id`) VALUES (@group_id, '6');
INSERT INTO `t_bank_users_groups` (`group_id`, `user_id`) VALUES (@group_id, '7');
INSERT INTO `t_bank_users_groups` (`group_id`, `user_id`) VALUES (@group_id, '8');
INSERT INTO `t_bank_users_groups` (`group_id`, `user_id`) VALUES (@group_id, '9');
INSERT INTO `t_bank_users_groups` (`group_id`, `user_id`) VALUES (@group_id, '10');
INSERT INTO `t_bank_users_groups` (`group_id`, `user_id`) VALUES (@group_id, '11');


INSERT INTO `t_bank_users_groups` (`group_id`, `user_id`) VALUES (@group_id, '6');
INSERT INTO `t_bank_users_groups` (`group_id`, `user_id`) VALUES (@group_id, '9');
INSERT INTO `t_bank_users_groups` (`group_id`, `user_id`) VALUES (@group_id, '10');
INSERT INTO `t_bank_users_groups` (`group_id`, `user_id`) VALUES (@group_id, '11');
INSERT INTO `t_bank_users_groups` (`group_id`, `user_id`) VALUES (@group_id, '12');
INSERT INTO `t_bank_users_groups` (`group_id`, `user_id`) VALUES (@group_id, '13');



INSERT INTO `t_flags` (`id`, `name`, `description`, `active_on_dev`, `active_on_prod`) VALUES
(NULL, 'bank-profile', 'Bank Portal', 1, 0);
SET @flag_id = last_insert_id();

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES(NULL, 'authcode', @flag_id, '');

SET @priv_id = last_insert_id();

INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @group_id, @flag_id, @priv_id, '1');

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES(NULL, 'changepassword', @flag_id, '');
SET @priv_id = last_insert_id();

INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @group_id, @flag_id, @priv_id, '1');

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES(NULL, 'checkbalance', @flag_id, '');
SET @priv_id = last_insert_id();

INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @group_id, @flag_id, @priv_id, '1');

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES(NULL, 'confirmationcode', @flag_id, '');
SET @priv_id = last_insert_id();

INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @group_id, @flag_id, @priv_id, '1');

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES(NULL, 'forgotpassword', @flag_id, '');
SET @priv_id = last_insert_id();

INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @group_id, @flag_id, @priv_id, '1');

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES(NULL, 'index', @flag_id, '');
SET @priv_id = last_insert_id();

INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @group_id, @flag_id, @priv_id, '1');

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES(NULL, 'login', @flag_id, '');
SET @priv_id = last_insert_id();

INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @group_id, @flag_id, @priv_id, '1');

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES(NULL, 'logout', @flag_id, '');
SET @priv_id = last_insert_id();

INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @group_id, @flag_id, @priv_id, '1');

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES(NULL, 'newpassword', @flag_id, '');
SET @priv_id = last_insert_id();

INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @group_id, @flag_id, @priv_id, '1');

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES(NULL, 'resendauthcode', @flag_id, '');
SET @priv_id = last_insert_id();

INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @group_id, @flag_id, @priv_id, '1');





INSERT INTO `t_flags` (`id`, `name`, `description`, `active_on_dev`, `active_on_prod`) VALUES
(NULL, 'bank-corp_kotak_customer', 'Kotak Amul Cardholders', 1, 0);
SET @flag_id = last_insert_id();

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'index', @flag_id, 'Kotak Amul Index page');
SET @priv_id = last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @group_id, @flag_id, @priv_id, '1');

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES  (NULL, 'search', @flag_id, 'Search Customer');
SET @priv_id = last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @group_id, @flag_id, @priv_id, '1');

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES  (NULL, 'approve', @flag_id, 'Approve Customer');
SET @priv_id = last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @group_id, @flag_id, @priv_id, '1');

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'reject', @flag_id, 'Reject Customer');
SET @priv_id = last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @group_id, @flag_id, @priv_id, '1');

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES  (NULL, 'view', @flag_id, 'Kotak Amul Customer detail view page');
SET @priv_id = last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @group_id, @flag_id, @priv_id, '1');

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'acceptdocument', @flag_id, 'Kotak Amul Customer Accept Physical document');
SET @priv_id = last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @group_id, @flag_id, @priv_id, '1');


INSERT INTO `t_flags` (`id`, `name`, `description`, `active_on_dev`, `active_on_prod`) VALUES (NULL, 'bank-filedownload', 'Manage download link for Customer docs', 1, 0);
SET @flag_id = last_insert_id();

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'index', @flag_id, 'Manage download link for Customer docs');         
SET @priv_id = last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @group_id, @flag_id, @priv_id, '1');


INSERT INTO `t_flags` (`id`, `name`, `description`, `active_on_dev`, `active_on_prod`) VALUES (NULL, 'bank-corp_kotak_reports', 'Kotak Amul Reports', 1, 0);
SET @flag_id = last_insert_id();

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'index', @flag_id, 'Reports Index for Kotak Amul');
SET @priv_id = last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @group_id, @flag_id, @priv_id, '1');

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'applications', @flag_id, 'Applications Report for Kotak Amul');
SET @priv_id = last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @group_id, @flag_id, @priv_id, '1');

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'exportapplications', @flag_id, 'Applications Report for Kotak Amul');  
SET @priv_id = last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @group_id, @flag_id, @priv_id, '1');


