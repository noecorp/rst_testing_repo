SET @flag_id := (SELECT id FROM `t_flags` WHERE name='operation-remit_reports');
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'remittancerefundyettoclaim', @flag_id, 'Remittance Refund Yet to claim');
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'exportremittancerefundyettoclaim', @flag_id, 'Export Remittance Refund Yet to claim');


ALTER TABLE `t_commission_report`
ADD COLUMN `transaction_fee`  decimal(11,2) NOT NULL AFTER `transaction_amount`,
ADD COLUMN `transaction_service_tax`  decimal(11,2) NOT NULL AFTER `transaction_fee`;
