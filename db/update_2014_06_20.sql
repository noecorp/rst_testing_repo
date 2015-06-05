ALTER TABLE `rat_corp_cardholders` ADD `status_ops` ENUM( 'pending', 'approved', 'rejected' ) NOT NULL DEFAULT 'pending' AFTER `date_failed` ;
-- ----------------------------
-- Table structure for `rat_corp_log_cardholder`
-- ----------------------------
DROP TABLE IF EXISTS `rat_corp_log_cardholder`;
CREATE TABLE `rat_corp_log_cardholder` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `rat_customer_id` int(11) unsigned NOT NULL,
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

ALTER TABLE `t_docs` DROP `doct_cardholder_id`;


ALTER TABLE `corporate_txn`
  DROP `txn_cardholder_id`,
  DROP `txn_remitter_id`,
  DROP `kotak_remitter_id`,
  DROP `remittance_request_id`,
  DROP `kotak_remittance_request_id`,
  DROP `insurance_claim_id`;

ALTER TABLE `rat_txn_customer` ADD `txn_corporate_id` INT( 11 ) UNSIGNED NOT NULL AFTER `txn_agent_id` ;

ALTER TABLE `rat_corp_load_request` CHANGE `load_channel` `load_channel` ENUM( 'medi-assist', 'ops', 'api', 'corporate' ) NOT NULL ;




SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-corp_ratnakar_cardholder'); 
SET @ops_id = '3';


INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'approvalpending', @flag_id, 'Approval Pending');
SET @priv_id = last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'exportapprovalpending', @flag_id, 'Export Approval Pending');
SET @priv_id = last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'bulkapprove', @flag_id, 'Bulk Approve');
SET @priv_id = last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');


INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'approve', @flag_id, 'Approve');
SET @priv_id = last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'reject', @flag_id, 'Reject');
SET @priv_id = last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

ALTER TABLE `corporate_balance`
ADD COLUMN `date_updated`  timestamp NULL ON UPDATE CURRENT_TIMESTAMP AFTER `date_modified`;

