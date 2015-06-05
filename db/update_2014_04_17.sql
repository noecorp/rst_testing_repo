INSERT INTO t_transaction_type VALUE('RRCP', 'Reversal Ratnakar Paytronics CardLoad', 'active', NOW(), 'no');

DROP TABLE IF EXISTS `t_bank_groups`;
CREATE TABLE `t_bank_groups` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(25) DEFAULT NULL,
  `parent_id` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_name` (`name`) USING BTREE,
  KEY `idx_parent_id` (`parent_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

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

ALTER TABLE `rat_corp_load_request`
MODIFY COLUMN `load_channel`  enum('medi-assist','ops','api') NOT NULL AFTER `txn_type`;

