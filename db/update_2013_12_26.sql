SET @ops_id := 3;
SET @flag_id := (SELECT id FROM `t_flags` WHERE name='operation-corp_kotak_customer' LIMIT 1);
Insert into `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'authorizedapplications', @flag_id, 'Download Authorized Applications');
SET @priv_id = last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');


ALTER TABLE `kotak_corp_cardholders`
ADD COLUMN `date_authorize`  datetime NULL AFTER `date_failed`;

