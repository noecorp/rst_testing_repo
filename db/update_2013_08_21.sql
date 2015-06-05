ALTER TABLE `rat_txn_customer`
ADD COLUMN `insurance_claim_id`  int(11) UNSIGNED NULL AFTER `product_id`;

ALTER TABLE `t_txn_agent`
ADD COLUMN `insurance_claim_id`  int(11) UNSIGNED NULL AFTER `remittance_request_id`;


ALTER TABLE `t_txn_agent`
MODIFY COLUMN `txn_customer_master_id`  int(11) UNSIGNED NULL AFTER `agent_id`;

ALTER TABLE `rat_corp_insurance_claim` CHANGE `status` `status` ENUM( 'pending', 'loaded', 'failed', 'cutoff', 'blocked', 'completed' ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT 'pending';

-- Adding PIN CODE AND CITY
/*
-- Pushed TO Production
INSERT INTO t_cities (`code`,`name`,pincode,state_code,country_code,std_code) VALUES ('SAT','Satara','415501','MAH','356','22');
INSERT INTO t_cities (`code`,`name`,pincode,state_code,country_code,std_code) VALUES ('SAT','Satara','415506','MAH','356','22');
INSERT INTO t_cities (`code`,`name`,pincode,state_code,country_code,std_code) VALUES ('SAT','Satara','415110','MAH','356','22');
INSERT INTO t_cities (`code`,`name`,pincode,state_code,country_code,std_code) VALUES ('RGD','Raigad','410222','MAH','356','22');
INSERT INTO t_cities (`code`,`name`,pincode,state_code,country_code,std_code) VALUES ('RGD','Raigad','410207','MAH','356','22');
*/

