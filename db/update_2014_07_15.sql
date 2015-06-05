SET @product_id := (SELECT id FROM `t_products` where unicode ='914' AND bank_id='3' );
SET @flag_id := (SELECT id FROM `t_flags` where name = 'agent-remit_ratnakar_beneficiary' );

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'exportcrnstatus', @flag_id, 'Export CRN Status Report');
SET @priv_id = last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');


SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-corp_ratnakar_cardholder'); 
SET @ops_id = '3';

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'exportcrnstatus', @flag_id, 'Export CRN Status Report');
SET @priv_id = last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-corp_kotak_cardholder'); 
SET @ops_id = '3';

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'crnstatus', @flag_id, 'CRN Status Report');
SET @priv_id = last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');


SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-corp_ratnakar_cardholder'); 
SET @ops_id = '3';

-- INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'crnstatus', @flag_id, 'CRN Status Report');
-- SET @priv_id = last_insert_id();
SET @priv_id = (select id from  `t_privileges`  where  `name`='crnstatus' AND  `flag_id`=@flag_id);
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');


INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'confirmtransferfund', @flag_id, 'Confirm Transfer fund request');
SET @privilege_id = last_insert_id();
Insert into `t_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @product_id, @flag_id, @privilege_id, '1');



SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-corp_kotak_cardholder'); 
SET @ops_id = '3';

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'exportcrnstatus', @flag_id, 'Export CRN Status Report');
SET @priv_id = last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');


SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-corp_ratnakar_cardholder'); 
SET @ops_id = '3';

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'exportcrnstatus', @flag_id, 'Export CRN Status Report');
SET @priv_id = last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');
