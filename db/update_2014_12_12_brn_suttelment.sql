ALTER TABLE `rat_corp_load_request` ADD `settlement_request_id` INT(11) UNSIGNED NOT NULL ,
ADD `settlement_response_id` INT(11) UNSIGNED NOT NULL ;

CREATE TABLE IF NOT EXISTS `rat_settlement_request` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `request_file_name` varchar(50) DEFAULT NULL,
  `date_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status` enum('started','active','inactive') NOT NULL DEFAULT 'started',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;



DROP TABLE IF EXISTS `rat_settlement_response`;
CREATE TABLE IF NOT EXISTS `rat_settlement_response` (
`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `sequence_no` int(11) unsigned NOT NULL,
  `txn_code` varchar(16) NOT NULL,
  `amount` varchar(10) NOT NULL,
  `value_date` varchar(10) NOT NULL,
  `batch_id` varchar(10) NOT NULL,
  `sending_branch` varchar(10) NOT NULL,
  `sender_act_type` varchar(15) NOT NULL,
  `sender_act_no` varchar(20) NOT NULL,
  `sender_act_name` varchar(25) NOT NULL,
  `bene_branch` varchar(35) NOT NULL,
  `bene_act_type` varchar(15) NOT NULL,
  `bene_act_no` varchar(20) NOT NULL,
  `bene_act_name` varchar(25) NOT NULL,
  `txn_status` varchar(20) NOT NULL,
  `remittance_origin` varchar(20) NOT NULL,
  `sender_remarks` varchar(50) NOT NULL,
  `file_name` varchar(20) NOT NULL,
  `by_ops_id` int(11) unsigned NOT NULL,
  `status` enum('success','failure','pending','rejected','duplicate','temp') NOT NULL DEFAULT 'pending',
  `failed_reason` varchar(30) NOT NULL,
  `date_created` datetime NOT NULL,
  `date_updated` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
 PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-corp_ratnakar_cardload'); 
SET @ops_id = '3';


INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'settlementresponse', @flag_id, 'Upload Settlement Response');
SET @priv_id = last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

INSERT INTO `t_cron` (`id`, `name`, `description`, `file_name`, `status`, `status_cron`, `date_updated`) VALUES ('53', 'Ratnakar Unsettled Batch creation', 'Ratnakar Unsettled Batch creation', 'RatSettlementBatchCreation.php', 'active', 'completed', 'NOW()');
INSERT INTO `t_cron` (`id`, `name`, `description`, `file_name`, `status`, `status_cron`, `date_updated`) VALUES ('54', 'Ratnakar Unsettlment Response', 'Ratnakar Unsettlment Response', 'RatSettlementResponse.php', 'active', 'completed', 'NOW()');

SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-reports'); 
SET @ops_id = '3';


INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'settledreport', @flag_id, 'Settled Report');
SET @priv_id = last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'exportsettledreport', @flag_id, 'Settled Report');
SET @priv_id = last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'unsettledreport', @flag_id, 'Unsettled Report');
SET @priv_id = last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'exportunsettledreport', @flag_id, 'Unsettled Report');
SET @priv_id = last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');
