ALTER TABLE `kotak_remittance_request` CHANGE `status` `status` ENUM( 'in_process', 'success', 'failure', 'incomplete', 'hold', 'refund' ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT 'in_process';

CREATE TABLE `kotak_remittance_refund` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `remitter_id` int(11) NOT NULL,
  `remittance_request_id` int(11) NOT NULL,
  `agent_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `amount` decimal(11,2) NOT NULL,
  `fee` decimal(11,2) NOT NULL,
  `service_tax` decimal(11,2) NOT NULL,
  `reversal_fee` decimal(11,2) NOT NULL,
  `reversal_service_tax` decimal(11,2) NOT NULL,
  `txn_code` int(11) unsigned NOT NULL,
  `status` enum('pending','success','failure') NOT NULL DEFAULT 'pending',
  `date_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

ALTER TABLE `kotak_remittance_request` CHANGE `fund_holder` `fund_holder` ENUM( 'remitter', 'beneficiary', 'agent', 'neft', 'ops' ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT 'remitter';

SET @product_id := (select id from t_products where name='Kotak Bank Shmart Transfer' LIMIT 1);
SET @flag_id := (select id from t_flags where name='agent-remit_kotak_beneficiary' LIMIT 1);


INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'failuretxn', @flag_id, 'List Failed Transactions');
SET @privilege_id = last_insert_id();
Insert into `t_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @product_id, @flag_id, @privilege_id, '1');


INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'refund', @flag_id, 'Refund Failed Transactions');
SET @privilege_id = last_insert_id();
Insert into `t_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @product_id, @flag_id, @privilege_id, '1');

ALTER TABLE `kotak_remittance_request` ADD `ops_id` INT( 11 ) UNSIGNED NOT NULL AFTER `agent_id`; 