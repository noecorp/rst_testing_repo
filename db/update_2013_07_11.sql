INSERT INTO `t_flags` (`id`, `name`, `description`, `active_on_dev`, `active_on_prod`) VALUES
(NULL, 'agent-hic_ratnakar_cardload', 'Load Card', 1, 0);
SET @flag_id = last_insert_id();

SET @product_id_val = (SELECT id FROM t_products WHERE name = 'MEDI ASSIST CARD');
INSERT INTO `t_privileges` (`name`, `flag_id`, `description`) VALUES ('search', @flag_id, 'Search HIC Cardholder');
SET @privilege_index_id = last_insert_id();
INSERT INTO `t_privileges` (`name`, `flag_id`, `description`) VALUES ('load', @flag_id, 'Load HIC Cardholder');
SET @privilege_search_id = last_insert_id();

INSERT INTO `t_product_privileges` (`product_id`, `flag_id`, `privilege_id`, `allow`) VALUES
(@product_id_val, @flag_id, @privilege_index_id, 1),
(@product_id_val, @flag_id, @privilege_search_id, 1);