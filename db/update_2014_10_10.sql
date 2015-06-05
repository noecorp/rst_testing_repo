SET @flag_id := (SELECT id FROM `t_flags` where name ='agent-corp_ratnakar_reports'); 
SET @priv_id := (SELECT id FROM `t_privileges` where name ='walletwisetransactionreport' AND flag_id=@flag_id); 
SET @product_id := (SELECT id from t_products WHERE const = 'RAT_HFCI' LIMIT 1); 
INSERT INTO `t_product_privileges` (`product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (@product_id, @flag_id, @priv_id, 1);

SET @priv_id := (SELECT id FROM `t_privileges` where name ='exportwalletwisetransactionreport' AND flag_id=@flag_id); 
SET @product_id := (SELECT id from t_products WHERE const = 'RAT_HFCI' LIMIT 1); 
INSERT INTO `t_product_privileges` (`product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (@product_id, @flag_id, @priv_id, 1);

UPDATE t_flags SET `flag_type`='corporate' WHERE `name` = 'corporate-corp_ratnakar_tid';