
INSERT INTO `t_flags` (`id`, `name`, `description`, `active_on_dev`, `active_on_prod`) VALUES (NULL, 'corporate-reports', 'Corporate Reports', '1', '0');
SET @flag_id :=  last_insert_id();


INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'corporatefunding', @flag_id, 'Corporate Funding');
SET @priv_id :=  last_insert_id();

INSERT INTO `t_corporate_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES
(null, 1, @flag_id, @priv_id, 1);
INSERT INTO `t_corporate_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES
(null, 2, @flag_id, @priv_id, 1);
INSERT INTO `t_corporate_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES
(null, 3, @flag_id, @priv_id, 1);
INSERT INTO `t_corporate_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES
(null, 4, @flag_id, @priv_id, 1);
INSERT INTO `t_corporate_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES
(null, 5, @flag_id, @priv_id, 1);
INSERT INTO `t_corporate_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES
(null, 6, @flag_id, @priv_id, 1);
INSERT INTO `t_corporate_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES
(null, 7, @flag_id, @priv_id, 1);
INSERT INTO `t_corporate_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES
(null, 8, @flag_id, @priv_id, 1);
INSERT INTO `t_corporate_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES
(null, 9, @flag_id, @priv_id, 1);
INSERT INTO `t_corporate_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES
(null, 10, @flag_id, @priv_id, 1);
INSERT INTO `t_corporate_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES
(null, 11, @flag_id, @priv_id, 1);
INSERT INTO `t_corporate_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES
(null, 12, @flag_id, @priv_id, 1);
INSERT INTO `t_corporate_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES
(null, 13, @flag_id, @priv_id, 1);
INSERT INTO `t_corporate_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES
(null, 14, @flag_id, @priv_id, 1);


INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'exportcorporatefunding', @flag_id, 'Export Corporate Funding');
SET @priv_id :=  last_insert_id();

INSERT INTO `t_corporate_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES
(null, 1, @flag_id, @priv_id, 1);
INSERT INTO `t_corporate_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES
(null, 2, @flag_id, @priv_id, 1);
INSERT INTO `t_corporate_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES
(null, 3, @flag_id, @priv_id, 1);
INSERT INTO `t_corporate_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES
(null, 4, @flag_id, @priv_id, 1);
INSERT INTO `t_corporate_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES
(null, 5, @flag_id, @priv_id, 1);
INSERT INTO `t_corporate_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES
(null, 6, @flag_id, @priv_id, 1);
INSERT INTO `t_corporate_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES
(null, 7, @flag_id, @priv_id, 1);
INSERT INTO `t_corporate_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES
(null, 8, @flag_id, @priv_id, 1);
INSERT INTO `t_corporate_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES
(null, 9, @flag_id, @priv_id, 1);
INSERT INTO `t_corporate_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES
(null, 10, @flag_id, @priv_id, 1);
INSERT INTO `t_corporate_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES
(null, 11, @flag_id, @priv_id, 1);
INSERT INTO `t_corporate_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES
(null, 12, @flag_id, @priv_id, 1);
INSERT INTO `t_corporate_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES
(null, 13, @flag_id, @priv_id, 1);
INSERT INTO `t_corporate_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES
(null, 14, @flag_id, @priv_id, 1);


SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-corp_kotak_cardholder'); 
SET @ops_id = '3';

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'addcardholderdocs', @flag_id, 'Add Cardholder Docs');
SET @priv_id = last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');




