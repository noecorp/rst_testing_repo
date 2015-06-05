SET @flag_id := (SELECT id FROM `t_flags` WHERE name='agent-corp_ratnakar_cardholder');
INSERT INTO `t_privileges` (`name`, `flag_id`, `description`) VALUES ('search', @flag_id, 'Search Corporate Cardholder');
SET @privilege_id = last_insert_id(); 
SET @product_id := (SELECT id FROM `t_products` WHERE name='MEDI ASSIST CARD');
INSERT INTO `t_product_privileges` (`product_id`, `flag_id`, `privilege_id`, `allow`) VALUES
(@product_id, @flag_id, @privilege_id, 1);
INSERT INTO `t_privileges` (`name`, `flag_id`, `description`) VALUES ('active', @flag_id, 'Activate Corporate Cardholder');
SET @privilege_id = last_insert_id(); 
INSERT INTO `t_product_privileges` (`product_id`, `flag_id`, `privilege_id`, `allow`) VALUES
(@product_id, @flag_id, @privilege_id, 1);

-- FIXED removed DB name from table name
UPDATE `t_products` SET `program_type` = 'Corp' WHERE `t_products`.`name` ='MEDI ASSIST CARD';
RENAME TABLE `rat_hic_cardholders` TO `rat_corp_cardholders` ;
RENAME TABLE `rat_hic_cardholder_details` TO `rat_corp_cardholder_details` ;
RENAME TABLE `rat_hic_hospital` TO `rat_corp_hospital` ;
RENAME TABLE `rat_hic_insurance_claim` TO `rat_corp_insurance_claim` ;
RENAME TABLE `rat_hic_terminal` TO `rat_corp_terminal` ;
RENAME TABLE `hic_insurance_claim` TO `corp_insurance_claim` ;
RENAME TABLE `log_rat_hic_hospital` TO `log_rat_corp_hospital` ;
RENAME TABLE `log_rat_hic_terminal` TO `log_rat_corp_terminal` ;

