UPDATE t_txn_agent SET txn_type = 'CDRL' WHERE txn_type = 'RCPL';
UPDATE t_txn_agent SET txn_type = 'RVLD' WHERE txn_type = 'RRCP';
UPDATE t_txn_agent SET txn_type = 'CATP' WHERE txn_type = 'RCTP' OR txn_type = 'BCTP';
UPDATE t_txn_agent SET txn_type = 'RATP' WHERE txn_type = 'RRCT' OR txn_type = 'BRCT';

UPDATE rat_txn_customer SET txn_type = 'CDRL' WHERE txn_type = 'RCPL';
UPDATE rat_txn_customer SET txn_type = 'RVLD' WHERE txn_type = 'RRCP';
UPDATE rat_txn_customer SET txn_type = 'CATP' WHERE txn_type = 'RCTP';
UPDATE rat_txn_customer SET txn_type = 'RATP' WHERE txn_type = 'RRCT';
UPDATE boi_txn_customer SET txn_type = 'CATP' WHERE txn_type = 'BCTP';
UPDATE boi_txn_customer SET txn_type = 'RATP' WHERE txn_type = 'BRCT';

INSERT INTO `t_transaction_type` VALUES ('CDDR', 'Card Debit', 'active', '2014-04-28 14:18:06', 'no');

UPDATE rat_corp_load_request SET txn_type = 'CDDR' WHERE txn_type = 'RCMD';
UPDATE t_txn_agent SET txn_type = 'CDDR' WHERE txn_type = 'RCMD';
UPDATE rat_txn_customer SET txn_type = 'CDDR' WHERE txn_type = 'RCMD';

ALTER TABLE `rat_corp_cardholders`
ADD COLUMN `narration`  varchar(100) NULL AFTER `date_blocked`,
ADD COLUMN `txn_code`  varchar(20) NULL AFTER `narration`;
