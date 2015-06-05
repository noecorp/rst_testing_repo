SET @ops_id := 3;

SET @flag_id := (SELECT id FROM `t_flags` WHERE name = 'operation-corp_ratnakar_cardholder');
SET @priv_id := (SELECT id FROM `t_privileges` WHERE `flag_id` = @flag_id AND name ='pendingkyc');
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

ALTER TABLE `kotak_remit_remitters` ADD `middle_name` VARCHAR( 100 ) NOT NULL AFTER `name` ,
ADD `last_name` VARCHAR( 100 ) NOT NULL AFTER `middle_name`;

ALTER TABLE `kotak_remit_remitters` ADD `address_line2` VARCHAR( 100 ) NOT NULL AFTER `address` ,
ADD `city` VARCHAR( 50 ) NOT NULL AFTER `address_line2` ,
ADD `state` VARCHAR( 50 ) NOT NULL AFTER `city` ,
ADD `pincode` INT( 10 ) NOT NULL AFTER `state`;

