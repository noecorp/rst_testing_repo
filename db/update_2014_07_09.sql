
SET @flag_id := (SELECT id FROM `t_flags` where name ='corporate-corp_kotak_cardholder');
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'downloadtxtfile', @flag_id, 'Download text file');
SET @priv_id = last_insert_id();
INSERT INTO `t_corporate_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 13, @flag_id, @priv_id, 1);
INSERT INTO `t_corporate_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 12, @flag_id, @priv_id, 1);


ALTER TABLE `t_txn_agent`  ADD `ratnakar_remitter_id` INT(11) UNSIGNED NULL AFTER `kotak_remitter_id`;
ALTER TABLE `t_txn_agent`  ADD `ratnakar_remittance_request_id` INT(11) UNSIGNED NULL AFTER `kotak_remittance_request_id`;

ALTER TABLE `t_txn_ops`  ADD `ratnakar_remitter_id` INT(11) UNSIGNED NULL AFTER `kotak_remitter_id`;
ALTER TABLE `t_txn_ops`  ADD `ratnakar_beneficiary_id` INT(11) UNSIGNED NULL AFTER `kotak_beneficiary_id`;
ALTER TABLE `t_txn_ops`  ADD `ratnakar_remittance_request_id` INT(11) UNSIGNED NULL AFTER `kotak_remittance_request_id`;

ALTER TABLE `t_change_status_log`  ADD `ratnakar_beneficiary_id` INT(11) UNSIGNED NOT NULL AFTER `kotak_beneficiary_id`;

