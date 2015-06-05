ALTER TABLE `kotak_corp_cardholders`  ADD `aml_status` TINYINT(1) NOT NULL DEFAULT '0' AFTER `status_ecs`;
ALTER TABLE `rat_corp_cardholders`  ADD `aml_status` TINYINT(1) NOT NULL DEFAULT '0' AFTER `status`;
ALTER TABLE `boi_corp_cardholders`  ADD `aml_status` TINYINT(1) NOT NULL DEFAULT '0' AFTER `prev_output_file_ids`;
ALTER TABLE `rat_remit_remitters`  ADD `aml_status` TINYINT(1) NOT NULL DEFAULT '0' AFTER `status`;
ALTER TABLE `t_remitters`  ADD `aml_status` TINYINT(1) NOT NULL DEFAULT '0' AFTER `status`;
ALTER TABLE `rat_beneficiaries`  ADD `aml_status` TINYINT(1) NOT NULL DEFAULT '0' AFTER `status`;
ALTER TABLE `t_beneficiaries`  ADD `aml_status` TINYINT(1) NOT NULL DEFAULT '0' AFTER `status`;

ALTER TABLE `t_agents` ADD `aml_status` TINYINT( 1 ) NOT NULL DEFAULT '0';
ALTER TABLE `kotak_remit_remitters` ADD `aml_status` TINYINT( 1 ) NOT NULL DEFAULT '0';
ALTER TABLE `kotak_beneficiaries` ADD `aml_status` TINYINT( 1 ) NOT NULL DEFAULT '0';
ALTER TABLE `kotak_beneficiaries` ADD INDEX ( `name` ) ;

CREATE TABLE IF NOT EXISTS `t_aml_master` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `dataid` int(11) NOT NULL,
  `first_name` varchar(512) NOT NULL,
  `second_name` varchar(512) NOT NULL,
  `full_name` varchar(512) NOT NULL,
  `fake_names` mediumtext NOT NULL,
  `nationality` mediumtext NOT NULL,
  `fake_address` mediumtext NOT NULL,
  `individual_date_of_birth` mediumtext NOT NULL,
  `individual_document` mediumtext NOT NULL,
  `comments1` mediumtext NOT NULL,
  `source` varchar(512) DEFAULT NULL,
  `by_ops_id` int(11) DEFAULT NULL,
  `date_created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 ;


INSERT INTO `t_flags` (id,`name`, `description`, `active_on_dev`, `active_on_prod`) VALUES
(null,'operation-aml', 'Anti Money Laundering', 1, 0);

SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-aml'); 
SET @ops_id = '3';
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES(NULL, 'uploadaml', @flag_id, 'Upload AML records');
SET @priv_id = last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES(NULL, 'amlbyops', @flag_id, 'Upload AML records');
SET @priv_id = last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES(NULL, 'displayaml', @flag_id, 'Upload AML records');
SET @priv_id = last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES(NULL, 'index', @flag_id, 'Upload AML records');
SET @priv_id = last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES(NULL, 'amlmatched', @flag_id, 'Upload AML records');
SET @priv_id = last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES(NULL, 'kotakremitters', @flag_id, 'Upload AML records');
SET @priv_id = last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES(NULL, 'kotakbeneficiaries', @flag_id, 'Upload AML records');
SET @priv_id = last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES(NULL, 'kotakremitterdetail', @flag_id, 'Upload AML records');
SET @priv_id = last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES(NULL, 'kotakbeneficiarydetail', @flag_id, 'Upload AML records');
SET @priv_id = last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES(NULL, 'amlrejectedagents', @flag_id, 'Upload AML records');
SET @priv_id = last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES(NULL, 'exportamlrejectedagents', @flag_id, 'Upload AML records');
SET @priv_id = last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES(NULL, 'exportkotakbeneficiaries', @flag_id, 'Upload AML records');
SET @priv_id = last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES(NULL, 'exportkotakremitters', @flag_id, 'Upload AML records');
SET @priv_id = last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES(NULL, 'kotakindex', @flag_id, 'Upload AML records');
SET @priv_id = last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES(NULL, 'kotakcardholders', @flag_id, 'Upload AML records');
SET @priv_id = last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES(NULL, 'kotakcardholderdetail', @flag_id, 'Upload AML records');
SET @priv_id = last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES(NULL, 'kotakduplicateremitters', @flag_id, 'Kotak Duplicate Remitters');
SET @priv_id = last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES(NULL, 'kotakremitterduplicatedetails', @flag_id, 'Kotak Duplicate Remitters Details');
SET @priv_id = last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES(NULL, 'kotakduplicatebeneficiary', @flag_id, 'Kotak Duplicate Beneficiary Details');
SET @priv_id = last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES(NULL, 'kotakbeneficiaryduplicatedetails', @flag_id, 'Kotak Duplicate Beneficiary Details');
SET @priv_id = last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES(NULL, 'kotakduplicatecardholder', @flag_id, 'Kotak Duplicate Beneficiary Details');
SET @priv_id = last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES(NULL, 'kotakcardholderduplicatedetails', @flag_id, 'Kotak Duplicate Beneficiary Details');
SET @priv_id = last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES(NULL, 'ratnakarindex', @flag_id, 'Ratnakar Index');
SET @priv_id = last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES(NULL, 'ratnakarbeneficiaries', @flag_id, 'Ratnakar Beneficiaries');
SET @priv_id = last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES(NULL, 'ratnakarremitters', @flag_id, 'Ratnakar Remitters');
SET @priv_id = last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES(NULL, 'ratnakarcardholders', @flag_id, 'Ratnakar Cardholders');
SET @priv_id = last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES(NULL, 'exportratnakarremitters', @flag_id, 'Export AML Ratnakar Remitters');
SET @priv_id = last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES(NULL, 'exportratnakarcardholders', @flag_id, 'Export AML Ratnakar Cardholders');
SET @priv_id = last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES(NULL, 'exportratnakarbeneficiaries', @flag_id, 'Export AML Ratnakar Beneficiaries');
SET @priv_id = last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES(NULL, 'ratnakarremitterdetail', @flag_id, 'Ratnakar Remitters Details');
SET @priv_id = last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES(NULL, 'ratnakarbeneficiarydetail', @flag_id, 'Ratnakar Beneficiary Details');
SET @priv_id = last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES(NULL, 'ratnakarcardholderdetail', @flag_id, 'AML Ratnakar Cardholder Details');
SET @priv_id = last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES(NULL, 'boiindex', @flag_id, 'Boi Index');
SET @priv_id = last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES(NULL, 'boibeneficiaries', @flag_id, 'Aml Boi Beneficiaries');
SET @priv_id = last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES(NULL, 'boiremitters', @flag_id, 'AML Boi Remitters');
SET @priv_id = last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES(NULL, 'boicardholders', @flag_id, 'AML Boi Cardholders');
SET @priv_id = last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES(NULL, 'exportboiremitters', @flag_id, 'Export AML Boi Remitters');
SET @priv_id = last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES(NULL, 'exportboicardholders', @flag_id, 'Export AML Boi Cardholders');
SET @priv_id = last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES(NULL, 'exportboibeneficiaries', @flag_id, 'Export AML Boi Beneficiaries');
SET @priv_id = last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES(NULL, 'boiremitterdetail', @flag_id, 'AML Boi Remitters Details');
SET @priv_id = last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES(NULL, 'boibeneficiarydetail', @flag_id, 'AML Boi Beneficiary Details');
SET @priv_id = last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES(NULL, 'boicardholderdetail', @flag_id, 'AML Boi Cardholder Details');
SET @priv_id = last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES(NULL, 'exportkotakcardholders', @flag_id, 'Export AML Kotak Cardholders');
SET @priv_id = last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'bankindex', @flag_id, 'Bank Index');
SET @priv_id = last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

INSERT INTO `t_cron` (`id`, `name`, `description`, `file_name`, `status`, `status_cron`, `date_updated`) VALUES (null, 'AML', 'AML', 'AgentAml.php', 'active', 'completed', CURRENT_TIMESTAMP);





