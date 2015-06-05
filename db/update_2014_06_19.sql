INSERT INTO `api_user` (`id`, `tp_user_id`, `username`, `password`) VALUES ('17', '17', 'oxigenuser', '3c916eb0d4150e054e6dd49e4f11624a11637a78');
INSERT INTO `api_user_ip` (`id`, `tp_user_id`,`tp_user_ip`) VALUES ('17', '17','127.0.0.1,122.160.80.129');
 

DROP TABLE IF EXISTS `t_sms`;
CREATE TABLE `t_sms` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int(11) unsigned NOT NULL,
  `val` varchar(50)  NOT NULL,
  `label` varchar(50) DEFAULT NULL,
  `user_id` int(11) unsigned DEFAULT NULL,
  `user_type` varchar(50) DEFAULT NULL,
  `method` varchar(100) NOT NULL,
  `request` text NOT NULL,
  `response` text NOT NULL,
  `exception` varchar(255) DEFAULT NULL,
  `ref_id` int(11) unsigned DEFAULT NULL,
  `user_ip` varchar(15) DEFAULT NULL,
  `time_request` datetime DEFAULT NULL,
  `time_response` datetime DEFAULT NULL,
  `date_created` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


INSERT INTO `t_cron` (`id`, `name`, `description`, `file_name`, `status`, `status_cron`, `date_updated`) VALUES ('39', 'Send Custom SMS', 'Cron to send custom SMS', 'SendCustomerSMS', 'active', 'completed', CURRENT_TIMESTAMP);

SET @flag_id :=  (select id from `t_flags` where `name` = 'corporate-corp_ratnakar_cardholder');
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'exportbatchstatus', @flag_id, 'Export Batch status');
SET @privileg_id :=  last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 4, @flag_id, @privileg_id, 1);
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 2, @flag_id, @privileg_id, 1);
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 3, @flag_id, @privileg_id, 1);


SET @flag_id :=  (select id from `t_flags` where `name` = 'corporate-corp_ratnakar_reports');
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'corporatefunding', @flag_id, 'Corporate Funding Report');
SET @privileg_id :=  last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 4, @flag_id, @privileg_id, 1);
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 2, @flag_id, @privileg_id, 1);
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 3, @flag_id, @privileg_id, 1);


SET @flag_id :=  (select id from `t_flags` where `name` = 'corporate-corp_ratnakar_reports');
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'exportcorporatefunding', @flag_id, 'Export Corporate Funding Report');
SET @privileg_id :=  last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 4, @flag_id, @privileg_id, 1);
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 2, @flag_id, @privileg_id, 1);
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 3, @flag_id, @privileg_id, 1);

ALTER TABLE `t_docs` ADD `doc_product_id` INT( 11 ) NOT NULL DEFAULT '0' AFTER `doc_corporate_id` ,,
ADD `doct_cardholder_id` INT( 11 ) NOT NULL DEFAULT '0' AFTER doc_product_id;

ALTER TABLE `rat_corp_cardholders` ADD `date_approval` DATETIME NOT NULL AFTER `date_updated` ;
ALTER TABLE `rat_corp_cardholders` ADD `status_ops` ENUM( 'pending', 'approved', 'rejected' ) NOT NULL DEFAULT 'approved' AFTER `date_failed` ;
