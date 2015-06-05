SET @ops_id := 3;

SET @flag_id := (SELECT id FROM `t_flags` WHERE name = 'operation-corp_ratnakar_cardholder');

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'searchma', @flag_id, 'Search Manual Adjustemtn');
SET @priv_id := (SELECT LAST_INSERT_ID());
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

SET @flag_id := (SELECT id FROM `t_flags` WHERE name = 'operation-corp_kotak_customer');

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'customerlist', @flag_id, 'Customer Listing');
SET @priv_id := (SELECT LAST_INSERT_ID());
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

