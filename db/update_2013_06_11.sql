INSERT INTO `t_transaction_type` VALUES ('RVRF', 'Reversal Remittance Fee', 'active', '2013-06-11 16:37:35', 'no');
INSERT INTO `t_transaction_type` VALUES ('RVST', 'Reversal Remittance Service Tax', 'active', '2013-06-11 16:38:07', 'no');


ALTER TABLE `t_remittance_refund`
ADD COLUMN `reversal_fee`  decimal(11,2) NOT NULL AFTER `service_tax`,
ADD COLUMN `reversal_service_tax`  decimal(11,2) NOT NULL AFTER `reversal_fee`;