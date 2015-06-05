ALTER TABLE `product_customer_limits`
ADD COLUMN `customer_type`  enum('kyc','non-kyc') NOT NULL AFTER `code`;

UPDATE product_customer_limits SET customer_type = 'kyc' WHERE code like 'KYC%';
UPDATE product_customer_limits SET customer_type = 'non-kyc' WHERE code like 'NKC%';

INSERT INTO `t_transaction_type` VALUES ('CATP', 'Corporate Authentication & Transaction Processing', 'active', '2014-04-28 14:18:06', 'no');
INSERT INTO `t_transaction_type` VALUES ('RATP', 'Reversal Authentication & Transaction Processing', 'active', '2014-04-28 14:18:50', 'no');
INSERT INTO `t_transaction_type` VALUES ('RVLD', 'Reversal Load', 'active', '2014-04-28 16:22:03', 'no');

UPDATE rat_corp_load_request SET txn_type = 'CDRL' WHERE txn_type = 'RCPL';
UPDATE rat_corp_load_request SET txn_type = 'RVLD' WHERE txn_type = 'RRCP';

UPDATE card_txn_processing SET txn_type = 'CATP' WHERE txn_type = 'RCTP' OR txn_type = 'BCTP';
UPDATE rat_corp_load_request SET txn_type = 'RATP' WHERE txn_type = 'RRCT' OR txn_type = 'BRCT';

ALTER TABLE `rat_corp_cardholders` CHANGE `status` `status` ENUM( 'active', 'inactive', 'ecs_pending', 'ecs_failed', 'blocked', 'activation_pending' ) NOT NULL DEFAULT 'ecs_pending';


SET @flag_id := (select id from t_flags where name='operation-corp_ratnakar_cardholder' LIMIT 1);
SET @ops_id = '3';

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'cardholderactivationreq', @flag_id, 'Bulk Upload of cardholder - Activation required');
SET @priv_id = last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');


SET @product_id := (select id from t_products where unicode = '810' LIMIT 1);
SET @flag_id := (select id from t_flags where name='agent-reports' LIMIT 1);



INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'agentfunding', @flag_id, 'Partner Funding Report');
SET @privilege_id = last_insert_id();
Insert into `t_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @product_id, @flag_id, @privilege_id, '1');


INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'exportagentfunding', @flag_id, 'Export Partner Funding Report');
SET @privilege_id = last_insert_id();
Insert into `t_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @product_id, @flag_id, @privilege_id, '1');

