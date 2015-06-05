SET @product_id := (SELECT id FROM `t_products` WHERE unicode='912');
SET @flag_id := (SELECT id FROM `t_flags` where name ='corporate-corp_ratnakar_cardholder');

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'enrolledcardholder', @flag_id, 'Enrolled Cardholder Page');
SET @priv_id = last_insert_id();
INSERT INTO `t_corporate_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 14, @flag_id, @priv_id, 1);


SET @product_id := (SELECT id FROM `t_products` WHERE unicode='915');

SET @flag_id := (SELECT id FROM `t_flags` where name ='corporate-corp_ratnakar_cardholder');

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'enrolledcardholder', @flag_id, 'Enrolled Cardholder Page');
SET @priv_id = last_insert_id();
INSERT INTO `t_corporate_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 14, @flag_id, @priv_id, 1);


SET @product_id := (SELECT id FROM `t_products` WHERE unicode='913');

SET @flag_id := (SELECT id FROM `t_flags` where name ='corporate-corp_ratnakar_cardholder');

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'enrolledcardholder', @flag_id, 'Enrolled Cardholder Page');
SET @priv_id = last_insert_id();
INSERT INTO `t_corporate_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 14, @flag_id, @priv_id, 1);
