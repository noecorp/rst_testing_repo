INSERT INTO `api_user` VALUES ('4', '4', 'kotakamul', 'ggr334g33bb21oi2345o', 'active', '2013-09-19 12:34:43');
INSERT INTO `api_user_ip` VALUES ('4', '4', '127.0.0.1,122.160.80.129,192.168.2.189', '0000-00-00 00:00:00');

SET @flag_id := (SELECT id FROM `t_flags` WHERE name='agent-agentfunding');

INSERT INTO `t_privileges` (`id` ,`name` ,`flag_id` ,`description`)
VALUES (NULL , 'viewfundrequest', @flag_id, 'Agent should be able to view his fund request details.');

SET @privilege_id = last_insert_id();
SET @product_id := (SELECT id FROM t_products WHERE unicode = '110' AND status = 'active' LIMIT 1);
Insert into `t_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @product_id, @flag_id, @privilege_id, '1');
SET @product_id := (SELECT id FROM t_products WHERE unicode = '210' AND status = 'active' LIMIT 1);
Insert into `t_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @product_id, @flag_id, @privilege_id, '1');
SET @product_id := (SELECT id FROM t_products WHERE unicode = '310' AND status = 'active' LIMIT 1);
Insert into `t_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @product_id, @flag_id, @privilege_id, '1');
SET @product_id := (SELECT id FROM t_products WHERE unicode = '410' AND status = 'active' LIMIT 1);
Insert into `t_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @product_id, @flag_id, @privilege_id, '1');
