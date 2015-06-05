INSERT INTO `t_flags` (`id`, `name`, `description`, `active_on_dev`, `active_on_prod`) VALUES (NULL, 'operation-remit_kotak_remitter', 'Remitter section for Kotak Remittance', '1', '0');
SET @flag_id = last_insert_id();

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'search', @flag_id, 'Search Kotak remitter Details');
SET @product_id := (SELECT id FROM `t_products` WHERE name='Kotak Bank Shmart Transfer');

INSERT INTO `t_product_privileges` (`product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (@product_id, '32', '225', 1);
