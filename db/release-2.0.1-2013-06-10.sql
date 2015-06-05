ALTER TABLE `t_commission_report` ADD COLUMN `transaction_fee`  decimal(11,2) NOT NULL AFTER `transaction_amount`;
ALTER TABLE `t_commission_report` ADD COLUMN `transaction_service_tax`  decimal(11,2) NOT NULL AFTER `transaction_fee`;
ALTER TABLE `t_commission_report` MODIFY COLUMN `commission_amount`  decimal(11,2) NOT NULL DEFAULT '0.00' ;

INSERT INTO `t_privileges` VALUES ('288', 'remittancerefundyettoclaim', '51', 'Remittance Refund Yet to claim');
INSERT INTO `t_privileges` VALUES ('289', 'exportremittancerefundyettoclaim', '51', 'Export Remittance Refund Yet to claim');
INSERT INTO `t_privileges` VALUES ('290', 'remittanceexception', '51', 'Remittance Exception');
INSERT INTO `t_privileges` VALUES ('291', 'exportremittanceexception', '51', 'Export Remittance Exception');