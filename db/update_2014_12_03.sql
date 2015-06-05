ALTER TABLE `rat_remittance_refund` MODIFY COLUMN `date_created`  timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `status`;

ALTER TABLE `t_remittance_refund` MODIFY COLUMN `date_created`  timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `status`;
