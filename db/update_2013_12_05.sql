ALTER TABLE `t_afn_no`
MODIFY COLUMN `afn_no`  varchar(16) NOT NULL FIRST ;


SET @ops_id := 3;

SET @flag_id := (SELECT id FROM `t_flags` WHERE name = 'operation-corp_kotak_customer');

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'uploadcrn', @flag_id, 'Bulk upload of CRN');
SET @priv_id := (SELECT LAST_INSERT_ID());
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

ALTER TABLE `kotak_corp_cardholders`
MODIFY COLUMN `status_ecs`  enum('pending','failure','success','waiting')  NOT NULL DEFAULT 'waiting' AFTER `status_ops`;


INSERT INTO `t_cron` (`id`, `name`, `description`, `file_name`, `status`) VALUES ('21', 'Update Kotak Amul Card details', 'Update Kotak Amul card number and card pack id', 'KtkCorpCRNUpdate.php', 'active');


INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'uploaddeliveryflag', @flag_id, 'Bulk upload of delivery flag file');
SET @priv_id := (SELECT LAST_INSERT_ID());
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

CREATE TABLE IF NOT EXISTS `delivery_file_master` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `card_number` varchar(16) NOT NULL,
  `card_pack_id` varchar(20) NOT NULL,
  `member_id` varchar(10) NOT NULL,
  `delivery_date` datetime NOT NULL,
  `delivery_status` enum('delivered','undelivered') NOT NULL DEFAULT 'undelivered',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;


INSERT INTO `t_cron` (`id`, `name`, `description`, `file_name`, `status`, `status_cron`, `date_updated`) VALUES ('22', 'Kotak Corp ECS customer registration', 'Kotak Corp ECS customer registration', 'KotakCorpECSRegn.php', 'active', 'completed', CURRENT_TIMESTAMP);

