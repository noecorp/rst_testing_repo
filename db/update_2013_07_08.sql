ALTER TABLE `t_agent_details` ADD `auth_email` VARCHAR( 100 ) NOT NULL AFTER `email` ;
SET @product_id := (SELECT id FROM `t_products` WHERE name='MEDI ASSIST CARD');

INSERT INTO `t_product_privileges` (`product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (@product_id, '28', '146', 1),
 (@product_id, '33', '173', 1), (@product_id, '35', '193', 1),
 (@product_id, '35', '194', 1), (@product_id, '33', '203', 1),
 (@product_id, '32', '208', 1), (@product_id, '32', '209', 1),
 (@product_id, '32', '210', 1), (@product_id, '33', '224', 1),
 (@product_id, '33', '225', 1), (@product_id, '33', '228', 1),
 (@product_id, '33', '239', 1), (@product_id, '33', '240', 1),
 (@product_id, '33', '253', 1), (@product_id, '33', '254', 1);

