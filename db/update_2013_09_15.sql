INSERT INTO `t_flags` (`id`, `name`,`description`) VALUES (NULL,'agent-agentfunding','Agent Funding');
SET @flag_id = LAST_INSERT_ID();
Insert into `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'index', @flag_id, 'Agent Funding');

SET @privilege_id = last_insert_id();
SET @product_id := (SELECT id FROM t_products WHERE unicode = '110' AND status = 'active' LIMIT 1);
Insert into `t_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @product_id, @flag_id, @privilege_id, '1');
SET @product_id := (SELECT id FROM t_products WHERE unicode = '210' AND status = 'active' LIMIT 1);
Insert into `t_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @product_id, @flag_id, @privilege_id, '1');
SET @product_id := (SELECT id FROM t_products WHERE unicode = '310' AND status = 'active' LIMIT 1);
Insert into `t_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @product_id, @flag_id, @privilege_id, '1');
SET @product_id := (SELECT id FROM t_products WHERE unicode = '410' AND status = 'active' LIMIT 1);
Insert into `t_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @product_id, @flag_id, @privilege_id, '1');



INSERT INTO `t_cron` (`id` ,`name` ,`description` ,`file_name` ,`status` ,`status_cron` ,`date_updated`)
VALUES (
NULL , 'Agent Funding To Check Duplicate Bank Statements ', 'Agent funding to check duplicate bank statements with condition with condations journal no./cheque no. and amount and status (''duplicate'' or ''unsettled'' or ''settled''). If record exist then mark its status duplicate else mark its status unsettled.', 'AgentFundingCheckDuplicate', 'active', 'completed',
CURRENT_TIMESTAMP
);





