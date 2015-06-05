SET @prod_id := (SELECT id FROM `t_products` where const ='RAT_REMIT'); 

SET @flag_id := (SELECT id FROM `t_flags` where name ='agent-remit_ratnakar_reports'); 

SET @priv_id := (SELECT id FROM `t_privileges` where name ='remittancecommission' and flag_id = @flag_id); 

SET @prod_priv_id := (SELECT id FROM `t_product_privileges` where privilege_id = @priv_id and flag_id = @flag_id and product_id = @prod_id); 

DELETE FROM `t_product_privileges` WHERE `t_product_privileges`.`id` = @prod_priv_id;

SET @priv_id := (SELECT id FROM `t_privileges` where name ='exportremittancecommission' and flag_id = @flag_id); 

SET @prod_priv_id := (SELECT id FROM `t_product_privileges` where privilege_id = @priv_id and flag_id = @flag_id and product_id = @prod_id); 

DELETE FROM `t_product_privileges` WHERE `t_product_privileges`.`id` = @prod_priv_id;


SET @product_id := (SELECT id FROM `t_products` WHERE unicode='914');
SET @flag_id := (SELECT id FROM `t_flags` where name ='agent-remit_ratnakar_reports'); 

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'distributormisreport', @flag_id, 'Distributor MIS Remittance Report');
SET @priv_id = last_insert_id();
INSERT INTO `t_product_privileges` (`product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (@product_id, @flag_id, @priv_id, 1);

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'exportdistributormisreport', @flag_id, 'Export Distributor MIS Remittance Report');
SET @priv_id = last_insert_id();
INSERT INTO `t_product_privileges` (`product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (@product_id, @flag_id, @priv_id, 1);


INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'superdistributormisreport', @flag_id, 'Super Distributor MIS Remittance Report');
SET @priv_id = last_insert_id();
INSERT INTO `t_product_privileges` (`product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (@product_id, @flag_id, @priv_id, 1);

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'exportsuperdistributormisreport', @flag_id, 'Export Super Distributor MIS Remittance Report');
SET @priv_id = last_insert_id();
INSERT INTO `t_product_privileges` (`product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (@product_id, @flag_id, @priv_id, 1);


SET @product_id := (SELECT id FROM `t_products` WHERE unicode='512');
SET @flag_id := (SELECT id FROM `t_flags` where name ='corporate-corp_kotak_cardholder'); 
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'opsrejected', @flag_id, 'Ops Rejected cardholder list');
SET @priv_id = last_insert_id();
INSERT INTO `t_corporate_product_privileges` VALUES (NULL, @product_id, @flag_id, @priv_id, '1');

SET @product_id := (SELECT id FROM `t_products` WHERE unicode='513');
SET @flag_id := (SELECT id FROM `t_flags` where name ='corporate-corp_kotak_cardholder'); 
SET @priv_id := (SELECT id FROM `t_privileges` WHERE name='opsrejected' AND flag_id =@flag_id );
INSERT INTO `t_corporate_product_privileges` VALUES (NULL, @product_id, @flag_id, @priv_id, '1');


SET @product_id := (SELECT id FROM `t_products` WHERE unicode='512');
SET @flag_id := (SELECT id FROM `t_flags` where name ='corporate-corp_kotak_cardholder'); 
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'view', @flag_id, 'Show Cardholder details');
SET @priv_id = last_insert_id();
INSERT INTO `t_corporate_product_privileges` VALUES (NULL, @product_id, @flag_id, @priv_id, '1');

SET @product_id := (SELECT id FROM `t_products` WHERE unicode='513');
SET @flag_id := (SELECT id FROM `t_flags` where name ='corporate-corp_kotak_cardholder'); 
SET @priv_id := (SELECT id FROM `t_privileges` WHERE name='view' AND flag_id =@flag_id );
INSERT INTO `t_corporate_product_privileges` VALUES (NULL, @product_id, @flag_id, @priv_id, '1');


SET @product_id := (SELECT id FROM `t_products` WHERE unicode='512');
SET @flag_id := (SELECT id FROM `t_flags` where name ='corporate-corp_kotak_cardholder'); 
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'edit', @flag_id, 'Edit Cardholder details');
SET @priv_id = last_insert_id();
INSERT INTO `t_corporate_product_privileges` VALUES (NULL, @product_id, @flag_id, @priv_id, '1');

SET @product_id := (SELECT id FROM `t_products` WHERE unicode='513');
SET @flag_id := (SELECT id FROM `t_flags` where name ='corporate-corp_kotak_cardholder'); 
SET @priv_id := (SELECT id FROM `t_privileges` WHERE name='edit' AND flag_id =@flag_id );
INSERT INTO `t_corporate_product_privileges` VALUES (NULL, @product_id, @flag_id, @priv_id, '1');


SET @product_id := (SELECT id FROM `t_products` WHERE unicode='912');
SET @flag_id := (SELECT id FROM `t_flags` where name ='corporate-corp_ratnakar_cardholder'); 
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'opsrejected', @flag_id, 'Ops Rejected Cardholder List');
SET @priv_id = last_insert_id();
INSERT INTO `t_corporate_product_privileges` VALUES (NULL, @product_id, @flag_id, @priv_id, '1');

SET @product_id := (SELECT id FROM `t_products` WHERE unicode='915');
SET @flag_id := (SELECT id FROM `t_flags` where name ='corporate-corp_ratnakar_cardholder'); 
SET @priv_id := (SELECT id FROM `t_privileges` WHERE name='opsrejected' AND flag_id =@flag_id );
INSERT INTO `t_corporate_product_privileges` VALUES (NULL, @product_id, @flag_id, @priv_id, '1');


SET @product_id := (SELECT id FROM `t_products` WHERE unicode='912');
SET @flag_id := (SELECT id FROM `t_flags` where name ='corporate-corp_ratnakar_cardholder'); 
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'view', @flag_id, 'Show Cardholder details');
SET @priv_id = last_insert_id();
INSERT INTO `t_corporate_product_privileges` VALUES (NULL, @product_id, @flag_id, @priv_id, '1');

SET @product_id := (SELECT id FROM `t_products` WHERE unicode='915');
SET @flag_id := (SELECT id FROM `t_flags` where name ='corporate-corp_ratnakar_cardholder'); 
SET @priv_id := (SELECT id FROM `t_privileges` WHERE name='view' AND flag_id =@flag_id );
INSERT INTO `t_corporate_product_privileges` VALUES (NULL, @product_id, @flag_id, @priv_id, '1');


SET @product_id := (SELECT id FROM `t_products` WHERE unicode='912');
SET @flag_id := (SELECT id FROM `t_flags` where name ='corporate-corp_ratnakar_cardholder'); 
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'edit', @flag_id, 'Edit Cardholder details');
SET @priv_id = last_insert_id();
INSERT INTO `t_corporate_product_privileges` VALUES (NULL, @product_id, @flag_id, @priv_id, '1');

SET @product_id := (SELECT id FROM `t_products` WHERE unicode='915');
SET @flag_id := (SELECT id FROM `t_flags` where name ='corporate-corp_ratnakar_cardholder'); 
SET @priv_id := (SELECT id FROM `t_privileges` WHERE name='edit' AND flag_id =@flag_id );
INSERT INTO `t_corporate_product_privileges` VALUES (NULL, @product_id, @flag_id, @priv_id, '1');





