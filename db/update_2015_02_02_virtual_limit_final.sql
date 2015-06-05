

/****************************** Virtual Balance SQL ****************************************/

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

 
 
/***********ACM**************/

SET @flag_id := (SELECT id FROM `t_flags` WHERE `name` = 'operation-reports');   

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'agentvirtualfunding', @flag_id, 'Agent Virtual Funding Reports');
SET @privileg_id := last_insert_id();  
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 3, @flag_id, @privileg_id, 1);



 


SET @flag_id := (SELECT id FROM `t_flags` WHERE `name` = 'operation-reports');   

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'exportagentvirtualfunding', @flag_id, 'Export Agent Virtual Funding Reports');
SET @privileg_id := last_insert_id();  
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 3, @flag_id, @privileg_id, 1);


 
/*Table structure for table `t_agent_virtual_closing_balance`*/

CREATE TABLE IF NOT EXISTS `t_agent_virtual_closing_balance` (
  `date` date NOT NULL,
  `agent_id` int(11) NOT NULL,
  `closing_balance` decimal(20,2) NOT NULL DEFAULT '0.00',
  `date_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_updated` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/* Indexes for table `t_agent_virtual_closing_balance`*/

ALTER TABLE `t_agent_virtual_closing_balance`
 ADD KEY `date` (`date`) USING BTREE, ADD KEY `agent_id` (`agent_id`) USING BTREE;


/*Add New Cron Entry */
/*Add New Cron Entry */
INSERT INTO `t_cron` (`id`, `name`, `description`, `file_name`, `status`, `status_cron`, `date_updated`) VALUES
(81, 'Update Agent Virtual Closing Balance', 'This cron will update agents Virtual balance', 'UpdateAgentVirtualClosingBalance.php', 'active', 'completed', CURRENT_TIMESTAMP);

INSERT INTO `t_cron` (`id`, `name`, `description`, `file_name`, `status`, `status_cron`, `date_updated`) VALUES (85, 'Generate Agent Virtual Balance Sheet', 'Generate Agent Virtual Balance Sheet : by this sheet we will create csv file to Generate Agent Virtual Balance Sheet', 'ReportAgentVirtualBS.php', 'active', 'completed', CURRENT_TIMESTAMP);


 


/*Add Field utr to save National UTR for Virtual Funding Request */

ALTER TABLE `agent_virtual_funding` ADD `utr` VARCHAR(50) NOT NULL AFTER `amount`;


ALTER TABLE `purse_master` ADD COLUMN `is_virtual` enum('yes','no') NULL DEFAULT 'no' AFTER `priority`;


/*ACM for Agent Portal to Request Agent Virtual funding for the Product DigiWallet And Corp*/
  
SET @flag_id := (SELECT id FROM `t_flags` WHERE name='agent-agentfunding');

INSERT INTO `t_privileges` (`id` ,`name` ,`flag_id` ,`description`) VALUES (NULL , 'requestvirtualfund', @flag_id, 'Agent can request for virtual fund');
SET @privilege_id = last_insert_id();

INSERT INTO `t_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) SELECT NULL,id,@flag_id, @privilege_id,'1' FROM `t_products` WHERE `program_type` IN ('DigiWallet','Corp');


/*ACM for Agent Portal to Request Agent Virtual funding for the Product DigiWallet And Corp*/
  
SET @flag_id := (SELECT id FROM `t_flags` WHERE name='agent-agentfunding');

INSERT INTO `t_privileges` (`id` ,`name` ,`flag_id` ,`description`) VALUES (NULL , 'virtualfundrequest', @flag_id, 'Agent can view his requests of virtual fund');
SET @privilege_id = last_insert_id();

INSERT INTO `t_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) SELECT NULL,id,@flag_id, @privilege_id,'1' FROM `t_products` WHERE `program_type` IN ('DigiWallet','Corp');
 

/***********ACM**************/


SET @flag_id := (SELECT id FROM `t_flags` WHERE `name` = 'operation-agentfunding');   
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'virtualfundrequests', @flag_id, 'Pending Agent Fund Requests For Virtual Balance');
SET @privileg_id := last_insert_id();  
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 3, @flag_id, @privileg_id, 1);



SET @flag_id := (SELECT id FROM `t_flags` WHERE `name` = 'operation-agentfunding');   
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'confirmvirtualfundrequset', @flag_id, 'Confirmation Page to Approve or reject Pending Agent Fund Requests of Virtual Balance');
SET @privileg_id := last_insert_id();  
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 3, @flag_id, @privileg_id, 1);




  
/***********ACM**************/


SET @flag_id := (SELECT id FROM `t_flags` WHERE `name` = 'operation-agentfunding');   
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'exportvirtualfundrequest', @flag_id, 'Export Pending Agent Fund Requests For Virtual Balance');
SET @privileg_id := last_insert_id();  
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 3, @flag_id, @privileg_id, 1);

 
 /***********ACM**************/
SET @flag_id := (SELECT id FROM `t_flags` WHERE `name` = 'operation-reports');   
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'unauthorizevirtualfund', @flag_id, 'Unauthorized Virtual Funding Reports');
SET @privileg_id := last_insert_id();  
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 3, @flag_id, @privileg_id, 1);

/***********ACM**************/
 
SET @flag_id := (SELECT id FROM `t_flags` WHERE `name` = 'operation-reports');   
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'exportunauthorizevirtualfund', @flag_id, 'Export Unauthorized Virtual Funding Reports');
SET @privileg_id := last_insert_id();  
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 3, @flag_id, @privileg_id, 1);

UPDATE purse_master SET is_virtual = 'yes' where code in ('SGP924', 'BPR923') LIMIT 2;





/******      Date :- 2015-02-13    *****************/




/* ACM for virtualwalletbalance in reports Section*/
SET @flag_id := (SELECT id FROM `t_flags` WHERE `name` = 'operation-reports');   
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'virtualwalletbalance', @flag_id, 'Virtual Wallet Balance Reports');
SET @privileg_id := last_insert_id();  
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 3, @flag_id, @privileg_id, 1);


/* ACM for exportvirtualwalletbalance in reports Section*/
SET @flag_id := (SELECT id FROM `t_flags` WHERE `name` = 'operation-reports');   
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'exportvirtualwalletbalance', @flag_id, 'Export Virtual Wallet Balance Reports');
SET @privileg_id := last_insert_id();  
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 3, @flag_id, @privileg_id, 1);