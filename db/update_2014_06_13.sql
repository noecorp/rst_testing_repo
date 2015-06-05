SET @flag_id := (select id from `t_flags` where `name` = 'operation-corp_boi_index');
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES(NULL, 'disbursementstatusreport', @flag_id, 'Disbursement Status Report');
SET @privileg_id :=  last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 9, @flag_id, @privileg_id, 1);
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 8, @flag_id, @privileg_id, 1);
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 3, @flag_id, @privileg_id, 1);

SET @flag_id := (select id from `t_flags` where `name` = 'operation-corp_boi_index');
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES(NULL, 'exportdisbursementstatusreport', @flag_id, 'Export Disbursement Status Report');
SET @privileg_id :=  last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 9, @flag_id, @privileg_id, 1);
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 8, @flag_id, @privileg_id, 1);
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 3, @flag_id, @privileg_id, 1);

SET @flag_id := (select id from `t_flags` where `name` = 'operation-corp_boi_index');
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES(NULL, 'disbursementcardloadreport', @flag_id, 'Disbursement Report - NSDC WalletCard load Status');
SET @privileg_id :=  last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 9, @flag_id, @privileg_id, 1);
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 8, @flag_id, @privileg_id, 1);
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 3, @flag_id, @privileg_id, 1);

SET @flag_id := (select id from `t_flags` where `name` = 'operation-corp_boi_index');
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES(NULL, 'exportdisbursementcardloadreport', @flag_id, 'Export Disbursement Report - NSDC WalletCard load Status');
SET @privileg_id :=  last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 9, @flag_id, @privileg_id, 1);
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 8, @flag_id, @privileg_id, 1);
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 3, @flag_id, @privileg_id, 1);

UPDATE `t_products` SET `ecs_product_code` = '10000024' WHERE `t_products`.`id` =3;