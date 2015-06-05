ALTER TABLE `kotak_remittance_status_log` CHANGE `status_old` `status_old` ENUM( 'in_process', 'success', 'failure', 'incomplete', 'hold', 'refund' ) NOT NULL DEFAULT 'in_process',
CHANGE `status_new` `status_new` ENUM( 'in_process', 'success', 'failure', 'incomplete', 'hold', 'refund' ) NOT NULL DEFAULT 'in_process';


SET @product_id := (select id from t_products where name='Kotak Bank Shmart Transfer' LIMIT 1);
SET @flag_id := (select id from t_flags where name='agent-Remit_Kotak_Remitter' LIMIT 1);


INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'transactions', @flag_id, 'See all of Transcation by Phone No.');
SET @privilege_id = last_insert_id();
Insert into `t_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @product_id, @flag_id, @privilege_id, '1');

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'transactioninfo', @flag_id, 'Transaction Detail Page');
SET @privilege_id = last_insert_id();
Insert into `t_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @product_id, @flag_id, @privilege_id, '1');

SET @flag_id := (SELECT id FROM `t_flags` WHERE name='operation-remit_kotak_remitter' LIMIT 1);
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'beneficiary', @flag_id, 'Beneficiary details');

INSERT INTO `t_flags` (`id`, `name`,`description`) VALUES (NULL,'operation-agentfunding','Agent Funding');
SET @flag_id = LAST_INSERT_ID();
Insert into `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'uploadbankstatement', @flag_id, 'Upload Bank Statement');
