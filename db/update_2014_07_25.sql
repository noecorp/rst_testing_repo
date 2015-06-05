SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-remit_reports'); 
SET @ops_id = '3';

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'beneficiaryexception', @flag_id, 'Beneficiary Exception for more than 1 lakh');
SET @priv_id = last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'exportbeneficiaryexception', @flag_id, 'Export Beneficiary Exception for more than 1 lakh');
SET @priv_id = last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

SET @product_id := (SELECT id FROM `t_products` WHERE unicode='912');

SET @flag_id := (SELECT id FROM `t_flags` where name ='corporate-corporatefunding'); 

SET @priv_id := (SELECT id FROM `t_privileges` where name ='fundrequest' AND flag_id=@flag_id); 
INSERT INTO `t_corporate_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES
(null, @product_id , @flag_id, @priv_id, 1);

SET @priv_id := (SELECT id FROM `t_privileges` where name ='viewfundrequest' AND flag_id=@flag_id); 
INSERT INTO `t_corporate_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES
(null, @product_id , @flag_id, @priv_id, 1);

SET @priv_id := (SELECT id FROM `t_privileges` where name ='requestfund' AND flag_id=@flag_id); 
INSERT INTO `t_corporate_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES
(null, @product_id , @flag_id, @priv_id, 1);

SET @priv_id := (SELECT id FROM `t_privileges` where name ='index' AND flag_id=@flag_id); 
INSERT INTO `t_corporate_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES
(null, @product_id , @flag_id, @priv_id, 1);


SET @flag_id := (SELECT id FROM `t_flags` where name ='corporate-corp_ratnakar_cardholder'); 

SET @priv_id := (SELECT id FROM `t_privileges` where name ='add' AND flag_id=@flag_id); 
INSERT INTO `t_corporate_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES
(null, @product_id , @flag_id, @priv_id, 1);

SET @priv_id := (SELECT id FROM `t_privileges` where name ='uploadcardholders' AND flag_id=@flag_id); 
INSERT INTO `t_corporate_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES
(null, @product_id , @flag_id, @priv_id, 1);

SET @flag_id := (SELECT id FROM `t_flags` where name ='corporate-corp_ratnakar_cardload'); 

SET @priv_id := (SELECT id FROM `t_privileges` where name ='corporateload' AND flag_id=@flag_id); 
INSERT INTO `t_corporate_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES
(null, @product_id , @flag_id, @priv_id, 1);

SET @priv_id := (SELECT id FROM `t_privileges` where name ='corporatesingleload' AND flag_id=@flag_id); 
INSERT INTO `t_corporate_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES
(null, @product_id , @flag_id, @priv_id, 1);

SET @flag_id := (SELECT id FROM `t_flags` where name ='corporate-linkedcorporates'); 

SET @priv_id := (SELECT id FROM `t_privileges` where name ='supercorporate' AND flag_id=@flag_id);
INSERT INTO `t_corporate_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES
(null, @product_id , @flag_id, @priv_id, 1);

SET @priv_id := (SELECT id FROM `t_privileges` where name ='subcorporatelisting' AND flag_id=@flag_id);
INSERT INTO `t_corporate_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES
(null, @product_id , @flag_id, @priv_id, 1);

SET @priv_id := (SELECT id FROM `t_privileges` where name ='fundtrfr' AND flag_id=@flag_id);
INSERT INTO `t_corporate_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES
(null, @product_id , @flag_id, @priv_id, 1);

SET @priv_id := (SELECT id FROM `t_privileges` where name ='retrievefund' AND flag_id=@flag_id);
INSERT INTO `t_corporate_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES
(null, @product_id , @flag_id, @priv_id, 1);

SET @priv_id := (SELECT id FROM `t_privileges` where name ='fundtrfrconfirm' AND flag_id=@flag_id);
INSERT INTO `t_corporate_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES
(null, @product_id , @flag_id, @priv_id, 1);

SET @priv_id := (SELECT id FROM `t_privileges` where name ='retrievetrfrconfirm' AND flag_id=@flag_id);
INSERT INTO `t_corporate_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES
(null, @product_id , @flag_id, @priv_id, 1);


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

SET @flag_id := (SELECT id FROM `t_flags` where name ='corporate-setting');

SET @priv_id := (SELECT id FROM `t_privileges` where name ='index' AND flag_id=@flag_id);
INSERT INTO `t_corporate_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES
(null, @product_id , @flag_id, @priv_id, 1);

SET @priv_id := (SELECT id FROM `t_privileges` where name ='updatemobile' AND flag_id=@flag_id);
INSERT INTO `t_corporate_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES
(null, @product_id , @flag_id, @priv_id, 1);

SET @priv_id := (SELECT id FROM `t_privileges` where name ='verification' AND flag_id=@flag_id);
INSERT INTO `t_corporate_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES
(null, @product_id , @flag_id, @priv_id, 1);

SET @priv_id := (SELECT id FROM `t_privileges` where name ='updateemail' AND flag_id=@flag_id);
INSERT INTO `t_corporate_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES
(null, @product_id , @flag_id, @priv_id, 1);

SET @priv_id := (SELECT id FROM `t_privileges` where name ='updatebasicinfo' AND flag_id=@flag_id);
INSERT INTO `t_corporate_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES
(null, @product_id , @flag_id, @priv_id, 1);


SET @flag_id := (SELECT id FROM `t_flags` where name ='corporate-reports');

SET @priv_id := (SELECT id FROM `t_privileges` where name ='corporatefunding' AND flag_id=@flag_id);
INSERT INTO `t_corporate_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES
(null, @product_id , @flag_id, @priv_id, 1);

SET @priv_id := (SELECT id FROM `t_privileges` where name ='exportcorporatefunding' AND flag_id=@flag_id);
INSERT INTO `t_corporate_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES
(null, @product_id , @flag_id, @priv_id, 1);

SET @flag_id := (SELECT id FROM `t_flags` where name ='corporate-corp_ratnakar_reports'); 

SET @priv_id := (SELECT id FROM `t_privileges` where name ='sampleload' AND flag_id = @flag_id); 
INSERT INTO `t_corporate_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES
(null, @product_id , @flag_id, @priv_id, 1);

SET @priv_id := (SELECT id FROM `t_privileges` where name ='exportsampleload' AND flag_id = @flag_id); 
INSERT INTO `t_corporate_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES
(null, @product_id , @flag_id, @priv_id, 1);

SET @priv_id := (SELECT id FROM `t_privileges` where name ='loadreport' AND flag_id = @flag_id); 
INSERT INTO `t_corporate_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES
(null, @product_id , @flag_id, @priv_id, 1);

SET @priv_id := (SELECT id FROM `t_privileges` where name ='activecards' AND flag_id = @flag_id); 
INSERT INTO `t_corporate_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES
(null, @product_id , @flag_id, @priv_id, 1);

SET @priv_id := (SELECT id FROM `t_privileges` where name ='exportloadreport' AND flag_id = @flag_id); 
INSERT INTO `t_corporate_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES
(null, @product_id , @flag_id, @priv_id, 1);

SET @priv_id := (SELECT id FROM `t_privileges` where name ='exportactivecards' AND flag_id = @flag_id); 
INSERT INTO `t_corporate_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES
(null, @product_id , @flag_id, @priv_id, 1);

SET @flag_id := (SELECT id FROM `t_flags` where name ='corporate-corp_ratnakar_cardholder');

SET @priv_id := (SELECT id FROM `t_privileges` where name ='downloadtxtfile' AND flag_id=@flag_id);
INSERT INTO `t_corporate_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES
(null, @product_id , @flag_id, @priv_id, 1);

SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-remit_reports'); 
SET @ops_id = '3';

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'beneficiaryexception', @flag_id, 'Beneficiary Exception for more than 1 lakh');
SET @priv_id = last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'exportbeneficiaryexception', @flag_id, 'Export Beneficiary Exception for more than 1 lakh');
SET @priv_id = last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');
