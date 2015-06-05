ALTER TABLE `kotak_remittance_request` ADD `cr_response` TEXT NOT NULL AFTER `hold_reason`;
ALTER TABLE `kotak_remittance_request` ADD `final_response` TEXT NOT NULL AFTER `cr_response`;

ALTER TABLE `kotak_remittance_request` CHANGE `hold_reason` `hold_reason` VARCHAR( 150 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL ,
CHANGE `cr_response` `cr_response` VARCHAR( 150 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL ,
CHANGE `final_response` `final_response` VARCHAR( 150 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL;



SET @flag_id := (SELECT id FROM t_flags WHERE name = 'operation-helpdesk');
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'kotakremittance', @flag_id, 'See all of Transcation by Phone No.');
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'txninfo', @flag_id, 'See Transcation by Txn Code');

