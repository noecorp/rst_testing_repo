 INSERT INTO `rct_master` (`id`, `ref_rec_type`, `ref_code`, `ref_desc`, `state_id`, `zone_name`, `city_id`, `brcode`) VALUES (NULL, '01', 'VIDIS', 'Vidisha', '10', 'Vidisha', '07592', NULL);

SET @flag_id := (select id from t_flags where name='operation-corp_boi_customer' LIMIT 1);
SET @ops_id = '3';

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'consolidatedreport', @flag_id, 'Add city for agent signup');
SET @priv_id = last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'exportconsolidatedreport', @flag_id, 'Add city for agent signup');
SET @priv_id = last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');


ALTER TABLE `global_purse_master`
DROP COLUMN `load_channel`;
