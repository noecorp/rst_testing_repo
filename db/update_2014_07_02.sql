
SET @flag_id := (SELECT id FROM `t_flags` where name ='corporate-corp_ratnakar_reports'); 

SET @priv_id := (SELECT id FROM `t_privileges` where name ='sampleload' AND flag_id = @flag_id); 
INSERT INTO `t_corporate_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES
(null, 14, @flag_id, @priv_id, 1);

SET @priv_id := (SELECT id FROM `t_privileges` where name ='exportsampleload' AND flag_id = @flag_id); 
INSERT INTO `t_corporate_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES
(null, 14, @flag_id, @priv_id, 1);

SET @priv_id := (SELECT id FROM `t_privileges` where name ='loadreport' AND flag_id = @flag_id); 
INSERT INTO `t_corporate_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES
(null, 14, @flag_id, @priv_id, 1);

SET @priv_id := (SELECT id FROM `t_privileges` where name ='activecards' AND flag_id = @flag_id); 
INSERT INTO `t_corporate_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES
(null, 14, @flag_id, @priv_id, 1);

SET @priv_id := (SELECT id FROM `t_privileges` where name ='exportloadreport' AND flag_id = @flag_id); 
INSERT INTO `t_corporate_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES
(null, 14, @flag_id, @priv_id, 1);

SET @priv_id := (SELECT id FROM `t_privileges` where name ='exportactivecards' AND flag_id = @flag_id); 
INSERT INTO `t_corporate_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES
(null, 14, @flag_id, @priv_id, 1);

SET @priv_id := (SELECT id FROM `t_privileges` where name ='sampleload' AND flag_id = @flag_id); 
INSERT INTO `t_corporate_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES
(null, 14, @flag_id, @priv_id, 1);

SET @priv_id := (SELECT id FROM `t_privileges` where name ='exportsampleload' AND flag_id = @flag_id); 
INSERT INTO `t_corporate_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES
(null, 14, @flag_id, @priv_id, 1);

SET @priv_id := (SELECT id FROM `t_privileges` where name ='exportsampleload' AND flag_id = @flag_id); 
INSERT INTO `t_corporate_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES
(null, 14, @flag_id, @priv_id, 1);





INSERT INTO `t_products` VALUES ('15', '3', 'RBL GENERIC GPR', 'RBL GENERIC GPR CARD', 'INR', '10000041', 'Corp', '913', 'RAT_GENERIC_GPR', 'yes', 'no', '101', '127000000001', NOW(), 'active');


INSERT INTO `purse_master` VALUES ('13', '3', '15', '1', 'RGN913', 'Ratnakar General GPR Wallet', 'Ratnakar General GPR Wallet', '50000', 'no', 'no', 'ops', '0', '0', '0', '500', '50000', '10', '50000', '90', '200000', '0', '0', 'mcc', 'no', '1', '50000', '50', '50000', '1500', '200000', '0', '0', '1', NOW(), NOW(), NOW(), '101', 'active');

INSERT INTO `product_customer_limits` VALUES ('21', '3', '15', 'KYC913', 'kyc', 'KYC Ratnakar Generic  GPR', 'KYC Ratnakar Generic GPR', '50000', '0', '50000', '50000', '200000', '0', '1', '50000', '50000', '200000', '0', NOW(), NOW(), NOW(), '101', 'active');
INSERT INTO `product_customer_limits` VALUES ('22', '3', '15', 'NKC913', 'non-kyc', 'Non-KYC Ratnakar Generic  GPR', 'Non KYC Ratnakar Generic GPR', '10000', '0', '10000', '10000', '10000', '10000', '1', '10000', '10000', '10000', '10000', NOW(), NOW(), NOW(), '101', 'active');

