SET @flag_id := (select id from `t_flags` where `name` = 'operation-corp_boi_index');
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES(NULL, 'editdisbursemenfile', @flag_id, 'Update ttum file details');
SET @privileg_id :=  last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 9, @flag_id, @privileg_id, 1);
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 8, @flag_id, @privileg_id, 1);
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 3, @flag_id, @privileg_id, 1);


ALTER TABLE `boi_disbursement_file` CHANGE `wallet_file_name` `updated_ttum_file_name` VARCHAR( 100 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL ;

ALTER TABLE `boi_disbursement_batch` ADD `load_request_id` INT( 11 ) NULL DEFAULT NULL ;

ALTER TABLE `report_tpmis` CHANGE `tp_code` `tp_code` VARCHAR(40) , CHANGE `agent_code` `agent_code` VARCHAR(40);
