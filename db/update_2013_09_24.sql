SET @flag_id := (SELECT id FROM `t_flags` WHERE name='agent-agentfunding');
INSERT INTO `t_privileges` (`id` ,`name` ,`flag_id` ,`description`)
VALUES (NULL , 'fundrequest', @flag_id, 'Agent should be able to view his fund requests. The requests should also display the status and remarks entered by operation if approved/rejected.');

SET @privilege_id = last_insert_id();
SET @product_id := (SELECT id FROM t_products WHERE unicode = '110' AND status = 'active' LIMIT 1);
Insert into `t_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @product_id, @flag_id, @privilege_id, '1');
SET @product_id := (SELECT id FROM t_products WHERE unicode = '210' AND status = 'active' LIMIT 1);
Insert into `t_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @product_id, @flag_id, @privilege_id, '1');
SET @product_id := (SELECT id FROM t_products WHERE unicode = '310' AND status = 'active' LIMIT 1);
Insert into `t_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @product_id, @flag_id, @privilege_id, '1');
SET @product_id := (SELECT id FROM t_products WHERE unicode = '410' AND status = 'active' LIMIT 1);
Insert into `t_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @product_id, @flag_id, @privilege_id, '1');



ALTER TABLE `agent_funding`
DROP COLUMN `journal_no`,
DROP COLUMN `cheque_no`,
CHANGE COLUMN `cheque_details` `funding_details`  varchar(255) NULL DEFAULT NULL ,
MODIFY COLUMN `ip_agent`  varchar(15) NOT NULL AFTER `comments`,
MODIFY COLUMN `date_request`  timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `ip_agent`,
MODIFY COLUMN `bank_statement_id`  int(11) UNSIGNED NOT NULL AFTER `date_request`,
CHANGE COLUMN `approved_by` `settlement_by`  enum('system','ops') NULL AFTER `bank_statement_id`,
CHANGE COLUMN `by_ops_id` `settlement_by_ops_id`  int(11) UNSIGNED NULL DEFAULT NULL AFTER `settlement_by`,
CHANGE COLUMN `ip_ops` `settlement_ip_ops`  varchar(15)  NULL DEFAULT NULL AFTER `settlement_by_ops_id`,
CHANGE COLUMN `date_settlement` `settlement_date`  timestamp NULL DEFAULT NULL AFTER `settlement_ip_ops`,
ADD COLUMN `funding_no`  varchar(50) NOT NULL AFTER `fund_transfer_type_id`;


ALTER TABLE `bank_statement`
DROP COLUMN `journal_no`,
DROP COLUMN `cheque_no`,
ADD COLUMN `fund_transfer_type_id`  int(11) UNSIGNED NOT NULL AFTER `description`,
ADD COLUMN `funding_no`  varchar(50) NOT NULL AFTER `fund_transfer_type_id`;
