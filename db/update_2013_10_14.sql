ALTER TABLE `t_bind_agent_limit`
ADD COLUMN `by_agent_id`  int(11) UNSIGNED NULL AFTER `by_ops_id`;

ALTER TABLE `t_bind_agent_product_commission`
ADD COLUMN `by_agent_id`  int(11) UNSIGNED NULL AFTER `by_ops_id`;


SET @flag_id :=  select id from t_flags where name='operation-reports';

INSERT INTO `t_privileges` (`name`, `flag_id`, `description`) VALUES ('agentinfo', @flag_id, 'Display agent info');

ALTER TABLE `rat_corp_cardholders` ADD `passport_expiry` DATE NULL AFTER `address_proof_number` ,
ADD `pan_number` VARCHAR( 10 ) NOT NULL AFTER `passport_expiry`;

ALTER TABLE `t_docs` ADD `doc_rat_customer_id` INT( 11 ) UNSIGNED NOT NULL AFTER `doc_bank_id` ,
ADD `doc_rat_corp_id` INT( 11 ) UNSIGNED NOT NULL AFTER `doc_rat_customer_id`;

ALTER TABLE `rat_corp_cardholders` ADD `address_proof_doc_id` INT( 11 ) UNSIGNED NOT NULL AFTER `address_proof_number`;

ALTER TABLE `rat_corp_cardholders` ADD `id_proof_doc_id` INT( 11 ) UNSIGNED NOT NULL AFTER `id_proof_number`;


SET @flag_id := (SELECT id FROM `t_flags` WHERE name = 'operation-corp_ratnakar_cardholder');
INSERT INTO `t_privileges` (`id` ,`name` ,`flag_id` ,`description`)
VALUES (NULL , 'addcardholderdocs', @flag_id, 'Add KYC documents for cardholder.');
