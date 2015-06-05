SET @product_id := (SELECT id FROM `t_products` WHERE unicode='921');

SET @flag_id := (SELECT id FROM `t_flags` where name ='corporate-corporatefunding'); 


SET @priv_id := (SELECT id FROM `t_privileges` where name ='fundrequest' AND flag_id=@flag_id); 
INSERT INTO `t_corporate_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES
(null, @product_id, @flag_id, @priv_id, 1);

SET @priv_id := (SELECT id FROM `t_privileges` where name ='viewfundrequest' AND flag_id=@flag_id); 
INSERT INTO `t_corporate_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES
(null, @product_id, @flag_id, @priv_id, 1);

SET @priv_id := (SELECT id FROM `t_privileges` where name ='requestfund' AND flag_id=@flag_id); 
INSERT INTO `t_corporate_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES
(null, @product_id, @flag_id, @priv_id, 1);

SET @priv_id := (SELECT id FROM `t_privileges` where name ='index' AND flag_id=@flag_id); 
INSERT INTO `t_corporate_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES
(null, @product_id, @flag_id, @priv_id, 1);


SET @flag_id := (SELECT id FROM `t_flags` where name ='corporate-linkedcorporates'); 

SET @priv_id := (SELECT id FROM `t_privileges` where name ='supercorporate' AND flag_id=@flag_id);
INSERT INTO `t_corporate_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES
(null, @product_id, @flag_id, @priv_id, 1);

SET @priv_id := (SELECT id FROM `t_privileges` where name ='subcorporatelisting' AND flag_id=@flag_id);
INSERT INTO `t_corporate_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES
(null, @product_id, @flag_id, @priv_id, 1);

SET @priv_id := (SELECT id FROM `t_privileges` where name ='fundtrfr' AND flag_id=@flag_id);
INSERT INTO `t_corporate_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES
(null, @product_id, @flag_id, @priv_id, 1);

SET @priv_id := (SELECT id FROM `t_privileges` where name ='retrievefund' AND flag_id=@flag_id);
INSERT INTO `t_corporate_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES
(null, @product_id, @flag_id, @priv_id, 1);

SET @priv_id := (SELECT id FROM `t_privileges` where name ='fundtrfrconfirm' AND flag_id=@flag_id);
INSERT INTO `t_corporate_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES
(null, @product_id, @flag_id, @priv_id, 1);

SET @priv_id := (SELECT id FROM `t_privileges` where name ='retrievetrfrconfirm' AND flag_id=@flag_id);
INSERT INTO `t_corporate_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES
(null, @product_id, @flag_id, @priv_id, 1);

SET @flag_id := (SELECT id FROM `t_flags` where name ='corporate-corp_ratnakar_reports'); 

SET @priv_id := (SELECT id FROM `t_privileges` where name ='loadreport' AND flag_id=@flag_id);
INSERT INTO `t_corporate_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES
(null, @product_id, @flag_id, @priv_id, 1);

SET @priv_id := (SELECT id FROM `t_privileges` where name ='exportloadreport' AND flag_id=@flag_id);
INSERT INTO `t_corporate_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES
(null, @product_id, @flag_id, @priv_id, 1);


SET @priv_id := (SELECT id FROM `t_privileges` where name ='activecards' AND flag_id=@flag_id);
INSERT INTO `t_corporate_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES
(null, @product_id, @flag_id, @priv_id, 1);


SET @priv_id := (SELECT id FROM `t_privileges` where name ='exportactivecards' AND flag_id=@flag_id);
INSERT INTO `t_corporate_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES
(null, @product_id, @flag_id, @priv_id, 1);

SET @priv_id := (SELECT id FROM `t_privileges` where name ='sampleload' AND flag_id=@flag_id);
INSERT INTO `t_corporate_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES
(null, @product_id, @flag_id, @priv_id, 1);


SET @priv_id := (SELECT id FROM `t_privileges` where name ='exportsampleload' AND flag_id=@flag_id);
INSERT INTO `t_corporate_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES
(null, @product_id, @flag_id, @priv_id, 1);

SET @flag_id := (SELECT id FROM `t_flags` where name ='corporate-setting'); 

SET @priv_id := (SELECT id FROM `t_privileges` where name ='index' AND flag_id=@flag_id);
INSERT INTO `t_corporate_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES
(null, @product_id, @flag_id, @priv_id, 1);


SET @priv_id := (SELECT id FROM `t_privileges` where name ='updatemobile' AND flag_id=@flag_id);
INSERT INTO `t_corporate_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES
(null, @product_id, @flag_id, @priv_id, 1);


SET @priv_id := (SELECT id FROM `t_privileges` where name ='verification' AND flag_id=@flag_id);
INSERT INTO `t_corporate_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES
(null, @product_id, @flag_id, @priv_id, 1);

SET @priv_id := (SELECT id FROM `t_privileges` where name ='updateemail' AND flag_id=@flag_id);
INSERT INTO `t_corporate_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES
(null, @product_id, @flag_id, @priv_id, 1);

SET @priv_id := (SELECT id FROM `t_privileges` where name ='updatebasicinfo' AND flag_id=@flag_id);
INSERT INTO `t_corporate_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES
(null, @product_id, @flag_id, @priv_id, 1);

SET @flag_id := (SELECT id FROM `t_flags` where name ='corporate-reports'); 

SET @priv_id := (SELECT id FROM `t_privileges` where name ='corporatefunding' AND flag_id=@flag_id);
INSERT INTO `t_corporate_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES
(null, @product_id, @flag_id, @priv_id, 1);


SET @priv_id := (SELECT id FROM `t_privileges` where name ='exportcorporatefunding' AND flag_id=@flag_id);
INSERT INTO `t_corporate_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES
(null, @product_id, @flag_id, @priv_id, 1);


SET @flag_id := (SELECT id FROM `t_flags` where name ='corporate-signup');

SET @priv_id := (SELECT id FROM `t_privileges` where name ='detailscomplete' AND flag_id=@flag_id);
INSERT INTO `t_corporate_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES
(null, @product_id , @flag_id, @priv_id, 1);

SET @priv_id := (SELECT id FROM `t_privileges` where name ='addbank' AND flag_id=@flag_id);
INSERT INTO `t_corporate_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES
(null, @product_id , @flag_id, @priv_id, 1);

SET @priv_id := (SELECT id FROM `t_privileges` where name ='addaddress' AND flag_id=@flag_id);
INSERT INTO `t_corporate_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES
(null, @product_id , @flag_id, @priv_id, 1);

SET @priv_id := (SELECT id FROM `t_privileges` where name ='addidentification' AND flag_id=@flag_id);
INSERT INTO `t_corporate_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES
(null, @product_id , @flag_id, @priv_id, 1);

SET @priv_id := (SELECT id FROM `t_privileges` where name ='addeducation' AND flag_id=@flag_id);
INSERT INTO `t_corporate_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES
(null, @product_id , @flag_id, @priv_id, 1);


SET @priv_id := (SELECT id FROM `t_privileges` where name ='add' AND flag_id=@flag_id);
INSERT INTO `t_corporate_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES
(null, @product_id , @flag_id, @priv_id, 1);

SET @priv_id := (SELECT id FROM `t_privileges` where name ='verification' AND flag_id=@flag_id);
INSERT INTO `t_corporate_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES
(null, @product_id , @flag_id, @priv_id, 1);

SET @priv_id := (SELECT id FROM `t_privileges` where name ='index' AND flag_id=@flag_id);
INSERT INTO `t_corporate_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES
(null, @product_id , @flag_id, @priv_id, 1);

ALTER TABLE `rat_corp_load_request` ADD `date_expiry` DATETIME NOT NULL AFTER `date_settlement`;

SET @corp_id := (SELECT * FROM `corporate_users` WHERE `first_name` LIKE 'CEquity');
INSERT INTO `corporate_bind_product_commission` (`id`, `corporate_id`, `product_id`, `plan_commission_id`, `plan_fee_id`, `by_ops_id`, `by_corporate_id`, `date_created`, `date_start`, `date_end`, `status`) VALUES (NULL, @corp_id, '21', '0', '0', '141', NULL, '2014-09-29 18:29:18', '2014-09-28', '0000-00-00', 'active'); 

ALTER TABLE `rat_remit_remitters` ADD `by_corporate_id` INT UNSIGNED NOT NULL AFTER `by_ops_id` ;

ALTER TABLE `rat_corp_load_request` CHANGE `date_expiry` `date_expiry` DATE NOT NULL;