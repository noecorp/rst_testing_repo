SET @flag_id := (SELECT id FROM `t_flags` WHERE name = 'operation-product');
INSERT INTO `t_privileges` (`id` ,`name` ,`flag_id` ,`description`)
VALUES (NULL , 'purseview', @flag_id, 'View purse details associated with the product');