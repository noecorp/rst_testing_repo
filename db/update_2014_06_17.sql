
ALTER TABLE `kotak_customer_product` ADD `by_corporate_id` INT( 11 ) UNSIGNED NOT NULL AFTER `by_agent_id` ;

SET @flag_id :=  (select id from `t_flags` where `name` = 'corporate-corp_ratnakar_cardload');
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'corporatesingleload', @flag_id, 'Single card load');
SET @privileg_id :=  last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 4, @flag_id, @privileg_id, 1);
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 2, @flag_id, @privileg_id, 1);
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 3, @flag_id, @privileg_id, 1);

SET @flag_id :=  (select id from `t_flags` where `name` = 'corporate-corp_ratnakar_reports');
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'exportloadreport', @flag_id, 'Export load requesy');
SET @privileg_id :=  last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 4, @flag_id, @privileg_id, 1);
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 2, @flag_id, @privileg_id, 1);
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 3, @flag_id, @privileg_id, 1);
