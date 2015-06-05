

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
 