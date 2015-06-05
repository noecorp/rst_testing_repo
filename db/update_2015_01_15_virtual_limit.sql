DROP TABLE IF EXISTS `agent_virtual_funding`;
CREATE TABLE `agent_virtual_funding` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `agent_id` int(11) unsigned NOT NULL,
  `amount` decimal(11,2) NOT NULL DEFAULT '0.00',
  `txn_type` char(4) NOT NULL DEFAULT 'AGFL',
  `comments` varchar(255) DEFAULT NULL,
  `ip_agent` varchar(15) DEFAULT NULL,
  `date_request` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `by_ops_id` int(11) unsigned DEFAULT NULL,
  `ip_ops` varchar(15) DEFAULT NULL,
  `date_funded` timestamp NULL DEFAULT NULL,
  `remarks` varchar(255) DEFAULT NULL,
  `status` enum('approved','pending','rejected','duplicate') NOT NULL DEFAULT 'pending',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `t_agent_virtual_balance`;
CREATE TABLE `t_agent_virtual_balance` (
  `agent_id` int(11) NOT NULL,
  `amount` decimal(20,2) NOT NULL DEFAULT '0.00',
  `block_amount` decimal(20,2) NOT NULL DEFAULT '0.00',
  `date_modified` datetime DEFAULT NULL,
  PRIMARY KEY (`agent_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `t_txn_ops`
ADD COLUMN `is_virtual`  enum('yes','no') NULL DEFAULT 'no' AFTER `txn_agent_id`;

ALTER TABLE `t_txn_agent`
ADD COLUMN `is_virtual`  enum('yes','no') NULL DEFAULT 'no' AFTER `agent_id`;

ALTER TABLE `rat_txn_customer`
ADD COLUMN `is_virtual`  enum('yes','no') NULL DEFAULT 'no' AFTER `txn_agent_id`;

ALTER TABLE `agent_virtual_funding` ADD `txn_code` VARCHAR(20) NOT NULL AFTER `txn_type`;


/***********ACM**************/

SET @flag_id := (SELECT id FROM `t_flags` WHERE `name` = 'operation-agents');   

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'virtuallimit', @flag_id, 'Agent Virtual Limit');
SET @privileg_id := last_insert_id();  
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 3, @flag_id, @privileg_id, 1);



SET @flag_id := (SELECT id FROM `t_flags` WHERE `name` = 'operation-agents');   
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'editvirtuallimit', @flag_id, 'Edit Agent Virtual Limit');
SET @privileg_id := last_insert_id();  
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 3, @flag_id, @privileg_id, 1);