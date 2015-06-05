SET @flag_id := (SELECT id FROM `t_flags` WHERE name='operation-agentsummary' LIMIT 1);

SET @priv_id := (SELECT id FROM `t_privileges` WHERE name='loadfund' AND flag_id = @flag_id LIMIT 1);
DELETE FROM t_flippers WHERE flag_id = @flag_id AND privilege_id = @priv_id ;
DELETE FROM t_privileges WHERE id = @priv_id LIMIT 1;

SET @priv_id := (SELECT id FROM `t_privileges` WHERE name='confirm' AND flag_id = @flag_id LIMIT 1);
DELETE FROM t_flippers WHERE flag_id = @flag_id AND privilege_id = @priv_id ;
DELETE FROM t_privileges WHERE id = @priv_id LIMIT 1;


SET @flag_id := (SELECT id FROM `t_flags` WHERE name='agent-agentfunding');

INSERT INTO `t_privileges` (`id` ,`name` ,`flag_id` ,`description`)
VALUES (NULL , 'requestfund', @flag_id, 'Agent Funding-agent can request for fund');

SET @privilege_id = last_insert_id();
SET @product_id := (SELECT id FROM t_products WHERE unicode = '110' AND status = 'active' LIMIT 1);
Insert into `t_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @product_id, @flag_id, @privilege_id, '1');
SET @product_id := (SELECT id FROM t_products WHERE unicode = '210' AND status = 'active' LIMIT 1);
Insert into `t_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @product_id, @flag_id, @privilege_id, '1');
SET @product_id := (SELECT id FROM t_products WHERE unicode = '310' AND status = 'active' LIMIT 1);
Insert into `t_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @product_id, @flag_id, @privilege_id, '1');
SET @product_id := (SELECT id FROM t_products WHERE unicode = '410' AND status = 'active' LIMIT 1);
Insert into `t_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @product_id, @flag_id, @privilege_id, '1');
