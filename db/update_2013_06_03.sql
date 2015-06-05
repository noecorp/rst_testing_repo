ALTER TABLE `t_remittance_request`
ADD COLUMN `batch_date`  datetime NOT NULL AFTER `batch_name`;

UPDATE t_remittance_request SET fund_holder = 'remitter' WHERE status = 'refund';


