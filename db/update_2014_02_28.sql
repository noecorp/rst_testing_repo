CREATE TABLE `output_file` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `batch_name` varchar(30) NOT NULL,
  `date_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

ALTER TABLE `boi_corp_cardholders`
ADD COLUMN `output_file_id`  int(11) UNSIGNED NOT NULL AFTER `training_partner_name`;


INSERT INTO `t_cron` VALUES ('29', 'BOI Output File', 'Cron will generate BOI Output File', 'GenerateOutputFile.php', 'active', 'completed', '2014-02-28 15:58:55');

SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-corp_boi_customer');

SET @ops_id = '3';

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'outputfile', @flag_id, 'BOI NSDC output file'); 
SET @priv_id = last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');
