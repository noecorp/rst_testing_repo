SET @product_id := (SELECT id FROM `t_products` WHERE unicode='914');

INSERT INTO `t_product_privileges` (`product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (@product_id, '28', '146', 1);
INSERT INTO `t_product_privileges` (`product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (@product_id, '33', '173', 1);
INSERT INTO `t_product_privileges` (`product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (@product_id, '35', '193', 1);
INSERT INTO `t_product_privileges` (`product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (@product_id, '35', '194', 1);
INSERT INTO `t_product_privileges` (`product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (@product_id, '33', '203', 1);
INSERT INTO `t_product_privileges` (`product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (@product_id, '32', '208', 1);
INSERT INTO `t_product_privileges` (`product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (@product_id, '32', '209', 1);
INSERT INTO `t_product_privileges` (`product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (@product_id, '32', '210', 1);
INSERT INTO `t_product_privileges` (`product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (@product_id, '33', '224', 1);
INSERT INTO `t_product_privileges` (`product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (@product_id, '33', '225', 1);
INSERT INTO `t_product_privileges` (`product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (@product_id, '33', '228', 1);
INSERT INTO `t_product_privileges` (`product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (@product_id, '33', '239', 1);
INSERT INTO `t_product_privileges` (`product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (@product_id, '33', '240', 1);
INSERT INTO `t_product_privileges` (`product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (@product_id, '33', '253', 1);
INSERT INTO `t_product_privileges` (`product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (@product_id, '33', '254', 1);

INSERT INTO `t_product_privileges` VALUES (NULL, @product_id, '64', '345', '1');
INSERT INTO `t_product_privileges` VALUES (NULL, @product_id, '64', '346', '1');
INSERT INTO `t_product_privileges` VALUES (NULL, @product_id, '64', '351', '1');
INSERT INTO `t_product_privileges` VALUES (NULL, @product_id, '64', '353', '1');

INSERT INTO `t_unicode_conf` (`unicode_initials`, `bank_unicode`, `product_unicode`) VALUES ('30091400', '300', '914');

SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-remit_ratnakar_remitter');

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'neftpending', @flag_id, 'NEFT Instructions Pending');
SET @priv_id = last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, '3', @flag_id, @priv_id, '1');

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'neftrequests', @flag_id, 'NEFT Instruction Batches');
SET @priv_id = last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, '3', @flag_id, @priv_id, '1');

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'neftbatch', @flag_id, 'NEFT Batch');
SET @priv_id = last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, '3', @flag_id, @priv_id, '1');

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'neftresponse', @flag_id, 'NEFT Response');
SET @priv_id = last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, '3', @flag_id, @priv_id, '1');

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'neftupdate', @flag_id, 'NEFT Instructions Update');
SET @priv_id = last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, '3', @flag_id, @priv_id, '1');

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'neftlog', @flag_id, 'NEFT Download Log');
SET @priv_id = last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, '3', @flag_id, @priv_id, '1');

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'neftbatchdetails', @flag_id, 'NEFT Batch Details');
SET @priv_id = last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, '3', @flag_id, @priv_id, '1');

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'neftprocessed', @flag_id, 'NEFT Processed');
SET @priv_id = last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, '3', @flag_id, @priv_id, '1');



DROP TABLE IF EXISTS `rat_remittance_request`;
CREATE TABLE IF NOT EXISTS `rat_remittance_request` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `remitter_id` int(11) NOT NULL,
  `beneficiary_id` int(11) NOT NULL,
  `agent_id` int(11) NOT NULL,
  `ops_id` int(11) NOT NULL,
  `product_id` int(11) unsigned NOT NULL,
  `amount` decimal(11,2) NOT NULL,
  `fee` decimal(11,2) NOT NULL,
  `service_tax` decimal(11,2) NOT NULL,
  `sender_msg` varchar(180) NOT NULL,
  `txn_code` int(11) unsigned NOT NULL,
  `status` enum('in_process','processed','success','failure','refund','incomplete') NOT NULL DEFAULT 'in_process',
  `fund_holder` enum('remitter','beneficiary','ops','agent','neft') NOT NULL DEFAULT 'ops',
  `batch_name` varchar(30) NOT NULL,
  `batch_date` datetime NOT NULL,
  `neft_processed` enum('yes','no') NOT NULL DEFAULT 'no',
  `neft_processed_ops_id` int(11) unsigned NOT NULL,
  `neft_processed_date` datetime NOT NULL,
  `neft_remarks` varchar(250) DEFAULT NULL,
  `status_sms` enum('pending','success','failure') NOT NULL DEFAULT 'pending',
  `is_complete` enum('yes','no') NOT NULL DEFAULT 'no',
  `date_created` datetime NOT NULL,
  `date_updated` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS `rat_log_neft_download`;
CREATE TABLE IF NOT EXISTS `rat_log_neft_download` (
  `batch_name` varchar(30) NOT NULL,
  `ops_id` int(11) unsigned NOT NULL,
  `ip` bigint(20) NOT NULL,
  `date_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


INSERT INTO `t_cron` (`id`, `name`, `description`, `file_name`, `status`, `status_cron`, `date_updated`) VALUES ('42', 'NEFT Batch creation for Ratnakar Remittance', 'NEFT Batch creation for Ratnakar Remittance', 'RatNEFTBatchCreation.php', 'active', 'completed', CURRENT_TIMESTAMP);
INSERT INTO `t_cron` (`id`, `name`, `description`, `file_name`, `status`, `status_cron`, `date_updated`) VALUES ('43', 'Generate remitter NEFT Requests for Ratnakar remittance', 'Cron to Generate remitter NEFT Requests for Ratnakar remittance', 'GenerateRATRemitterNEFTRequest.php', 'active', 'completed', CURRENT_TIMESTAMP);


ALTER TABLE `rat_corp_cardholder_batch` ADD `by_agent_id` INT( 11 ) NULL DEFAULT NULL AFTER `by_ops_id` ;

SET @flag_id := (SELECT id FROM `t_flags` where name ='corporate-corp_ratnakar_cardholder');

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'downloadtxtfile', @flag_id, 'Force Download text file');
SET @priv_id = last_insert_id();
INSERT INTO `t_corporate_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 14, @flag_id, @priv_id, 1);


