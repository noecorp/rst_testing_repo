 
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
INSERT INTO `t_cron` (`id`, `name`, `description`, `file_name`, `status`, `status_cron`, `date_updated`) VALUES
(81, 'Update Agent Virtual Closing Balance', 'This cron will find the agents last active Virtual balance and will update in t_agent_virtual_closing_balance if not updated already. ', 'UpdateAgentVirtualClosingBalance.php', 'active', 'completed', '2015-01-19 08:58:27');