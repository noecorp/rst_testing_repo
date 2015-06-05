SET @flag_id := (SELECT id FROM `t_flags` WHERE name = 'operation-product');
INSERT INTO `t_privileges` (`id` ,`name` ,`flag_id` ,`description`)
VALUES (NULL , 'editpurse', @flag_id, 'Edit purse details associated with the product');

ALTER TABLE `purse_master` CHANGE `max_balance` `max_balance` INT( 11 ) UNSIGNED NOT NULL;

CREATE TABLE `agent_fund_transfer` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `txn_code` int(11) unsigned NOT NULL,    
  `agent_id` int(11) NOT NULL,
  `txn_agent_id` int(11) NOT NULL,
  `amount` decimal(11,2) NOT NULL,
  `txn_type` char(4) NOT NULL,
  `status` enum('pending','success','failure') NOT NULL DEFAULT 'pending',
  `date_created` datetime NOT NULL,
  `date_updated` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



INSERT INTO `t_transaction_type` (`typecode`, `name`) VALUES ('AAFT', 'Agent to Agent Fund Transfer');
INSERT INTO `t_transaction_type` (`typecode`, `name`) VALUES ('RAFT', 'Rerversal Agent to Agent Fund Transfer');
INSERT INTO `t_transaction_type` (`typecode`, `name`) VALUES ('RAFL', 'Reversal Agent Fund Load');

