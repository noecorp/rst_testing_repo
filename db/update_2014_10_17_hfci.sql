UPDATE `purse_master` SET `status` = 'inactive' WHERE `code` = 'HCR922';

SET @product_id := (SELECT id FROM `t_products` WHERE unicode='922');
SET @flag_id := (SELECT id FROM `t_flags` where name ='agent-reports'); 
SET @priv_id := (SELECT id FROM `t_privileges` where name = 'exportbalancesheet' AND flag_id=@flag_id);
INSERT INTO `t_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES
(null, @product_id, @flag_id, @priv_id, 1);

SET @priv_id := (SELECT id FROM `t_privileges` where name = 'exportdailytxn' AND flag_id=@flag_id);
INSERT INTO `t_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES
(null, @product_id, @flag_id, @priv_id, 1);
