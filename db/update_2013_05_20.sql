SET @flag_id := (SELECT id FROM `t_flags` WHERE name='operation-remit_boi_remitter');
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'neftlog', @flag_id, 'Neft Log');

CREATE TABLE IF NOT EXISTS `t_log_neft_download` (
  `batch_name` varchar(30) NOT NULL,
  `ops_id` int(11) unsigned NOT NULL,
  `ip` bigint(20) NOT NULL,
  `date_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


UPDATE t_flags set name='operation-history' where name='operation-logs' limit 1;

SET @flag_id := (SELECT id FROM `t_flags` WHERE name='agent-reports');
SET @priv_id := (SELECT id FROM `t_privileges` WHERE name='index' AND flag_id = @flag_id);
INSERT INTO `t_product_privileges` (`id`, `product_id`, `flag_id`, privilege_id, `allow`) VALUES (NULL, 2, @flag_id, @priv_id, 1);

SET @priv_id := (SELECT id FROM `t_privileges` WHERE name='agentfundrequests' AND flag_id = @flag_id);
INSERT INTO `t_product_privileges` (`id`, `product_id`, `flag_id`, privilege_id, `allow`) VALUES (NULL, 2, @flag_id, @priv_id, 1);