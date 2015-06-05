ALTER TABLE `rat_corp_load_request` ADD `by_corporate_id` INT( 11 ) NULL DEFAULT NULL AFTER `by_ops_id`;


SET @flag_id :=  select id from `t_flags` where `name` = 'corporate-linkedcorporates';
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'fundtrfr', @flag_id, 'Display registered users list');
SET @privileg_id :=  last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 4, @flag_id, @privileg_id, 1);
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 2, @flag_id, @privileg_id, 1);
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'retrievefund', @flag_id, 'Display registered users list');
SET @privileg_id :=  last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 4, @flag_id, @privileg_id, 1);
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 2, @flag_id, @privileg_id, 1);
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'fundtrfrconfirm', @flag_id, 'Display registered users list');
SET @privileg_id :=  last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 4, @flag_id, @privileg_id, 1);
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 2, @flag_id, @privileg_id, 1);
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'retrievetrfrconfirm', @flag_id, 'Display registered users list');
SET @privileg_id :=  last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 4, @flag_id, @privileg_id, 1);
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 2, @flag_id, @privileg_id, 1);