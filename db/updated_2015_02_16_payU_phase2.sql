CREATE TABLE IF NOT EXISTS `t_agent_balance_settings` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `agent_id` int(11) unsigned NOT NULL,
  `description` tinytext,
  `value` varchar(20) NOT NULL,
  `currency` char(3) DEFAULT NULL,
  `by_ops_id` int(11) unsigned NOT NULL,
  `ip` varchar(15) NOT NULL,
  `type` enum('min_balance','max_balance') NOT NULL DEFAULT 'min_balance',
  `date_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB ;


SET @flag_id := (SELECT id FROM `t_flags` WHERE name='operation-agents' LIMIT 1);
INSERT INTO `t_privileges` VALUES (NULL, 'agentbalancealert', @flag_id, 'Agent Balance Alert');
SET @priv_id := (SELECT id FROM `t_privileges` WHERE name='agentbalancealert' AND flag_id = @flag_id LIMIT 1);
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 3, @flag_id, @priv_id, 1);


SET @flag_id := (SELECT id FROM `t_flags` WHERE name='operation-agents' LIMIT 1);
INSERT INTO `t_privileges` VALUES (NULL, 'addagentbalancealert', @flag_id, 'Add Agent Balance alert');
SET @priv_id := (SELECT id FROM `t_privileges` WHERE name='addagentbalancealert' AND flag_id = @flag_id LIMIT 1);
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 3, @flag_id, @priv_id, 1);
