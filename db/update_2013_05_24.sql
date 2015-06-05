SET @flag_id := (SELECT id FROM `t_flags` WHERE name='agent-reports');
SET @priv_id := (SELECT id FROM `t_privileges` WHERE name='agentsummary' AND flag_id = @flag_id );
INSERT INTO `t_product_privileges` (`id`, `product_id`, `flag_id`, privilege_id, `allow`) VALUES (NULL, 2, @flag_id, @priv_id, 1);
SET @priv_id := (SELECT id FROM `t_privileges` WHERE name='agentcommissionsummary' AND flag_id = @flag_id );
INSERT INTO `t_product_privileges` (`id`, `product_id`, `flag_id`, privilege_id, `allow`) VALUES (NULL, 2, @flag_id, @priv_id, 1);