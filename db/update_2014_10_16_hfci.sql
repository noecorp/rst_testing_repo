INSERT INTO `shmart`.`t_bind_agent_product_commission` (`id`, `agent_id`, `product_id`, `product_limit_id`, `plan_commission_id`, `plan_fee_id`, `by_ops_id`, `by_agent_id`, `date_created`, `date_start`, `date_end`, `status`) VALUES (NULL, '438', '22', '0', '0', '0', '101', NULL, CURRENT_TIMESTAMP, '2014-10-16', '', 'active');


SET @product_id := (SELECT id FROM `t_products` WHERE unicode='922');

INSERT INTO `t_product_privileges` (`product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (@product_id, '28', '146', 1);
INSERT INTO `t_product_privileges` (`product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (@product_id, '33', '173', 1);
INSERT INTO `t_product_privileges` (`product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (@product_id, '35', '193', 1);
INSERT INTO `t_product_privileges` (`product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (@product_id, '35', '194', 1);
INSERT INTO `t_product_privileges` (`product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (@product_id, '33', '203', 1);
INSERT INTO `t_product_privileges` (`product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (@product_id, '32', '208', 1);
INSERT INTO `t_product_privileges` (`product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (@product_id, '32', '209', 1);
INSERT INTO `t_product_privileges` (`product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (@product_id, '32', '210', 1);
INSERT INTO `t_product_privileges` (`product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (@product_id, '33', '224', 1);
INSERT INTO `t_product_privileges` (`product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (@product_id, '33', '225', 1);
INSERT INTO `t_product_privileges` (`product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (@product_id, '33', '228', 1);
INSERT INTO `t_product_privileges` (`product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (@product_id, '33', '239', 1);
INSERT INTO `t_product_privileges` (`product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (@product_id, '33', '240', 1);
INSERT INTO `t_product_privileges` (`product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (@product_id, '33', '253', 1);
INSERT INTO `t_product_privileges` (`product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (@product_id, '33', '254', 1);
INSERT INTO `t_product_privileges` (`product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (@product_id, '33', '433', 1);
INSERT INTO `t_product_privileges` (`product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (@product_id, '33', '434', 1);


INSERT INTO `t_product_privileges` VALUES (NULL, @product_id, '64', '345', '1');
INSERT INTO `t_product_privileges` VALUES (NULL, @product_id, '64', '346', '1');
INSERT INTO `t_product_privileges` VALUES (NULL, @product_id, '64', '351', '1');
INSERT INTO `t_product_privileges` VALUES (NULL, @product_id, '64', '353', '1');


SET @flag_id := (SELECT id FROM `t_flags` where name ='agent-corp_ratnakar_reports'); 
SET @product_id := (SELECT id from t_products WHERE const = 'RAT_HFCI' LIMIT 1); 

SET @priv_id := (SELECT id FROM `t_privileges` where name ='loadreport' AND flag_id=@flag_id); 
INSERT INTO `t_product_privileges` (`product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (@product_id, @flag_id, @priv_id, 1);

SET @priv_id := (SELECT id FROM `t_privileges` where name ='exportloadreport' AND flag_id=@flag_id); 
INSERT INTO `t_product_privileges` (`product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (@product_id, @flag_id, @priv_id, 1);

SET @priv_id := (SELECT id FROM `t_privileges` where name ='customerregistration' AND flag_id=@flag_id); 
INSERT INTO `t_product_privileges` (`product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (@product_id, @flag_id, @priv_id, 1);

SET @priv_id := (SELECT id FROM `t_privileges` where name ='exportcustomerregistration' AND flag_id=@flag_id); 
INSERT INTO `t_product_privileges` (`product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (@product_id, @flag_id, @priv_id, 1);

