SET @flag_id :=  (select id from `t_flags` where `name` = 'corporate-corp_ratnakar_reports');
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'exportactivecards', @flag_id, 'Export load requesy');
SET @privileg_id :=  last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 4, @flag_id, @privileg_id, 1);
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 2, @flag_id, @privileg_id, 1);
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 3, @flag_id, @privileg_id, 1);


 DROP TABLE IF EXISTS `kotak_batch_adjustment`;
 CREATE  TABLE `kotak_batch_adjustment` (  `id` int( 11  )  unsigned NOT  NULL  AUTO_INCREMENT ,
 `product_id` int( 11  )  NOT  NULL ,
 `card_number` varchar( 16  )  NOT  NULL ,
 `customer_master_id` int( 11  )  unsigned NOT  NULL ,
 `cardholder_id` int( 11  )  unsigned NOT  NULL ,
 `purse_master_id` int( 11  )  unsigned NOT  NULL ,
 `customer_purse_id` int( 11  )  unsigned NOT  NULL ,
 `txn_type` char( 4  )  NOT  NULL ,
 `wallet_code` varchar( 20  )  NOT  NULL DEFAULT  'free',
 `mode` varchar( 20  )  NOT  NULL ,
 `amount` decimal( 20, 2  )  NOT  NULL ,
 `rrn` varchar( 15  )  NOT  NULL ,
 `status` enum(  'failed',  'success',  'duplicate',  'pending',  'in_process',  'temp'  )  NOT  NULL DEFAULT  'pending',
 `narration` varchar( 200  )  DEFAULT NULL ,
 `txn_code` int( 11  ) unsigned  DEFAULT NULL ,
 `failed_reason` varchar( 100  )  DEFAULT NULL ,
 `file` varchar( 50  )  NOT  NULL ,
 `by_ops_id` int( 11  ) unsigned  DEFAULT NULL ,
 `date_created` datetime  DEFAULT NULL ,
 `date_failed` datetime  DEFAULT NULL ,
 `date_updated` timestamp NULL  DEFAULT NULL  ON  UPDATE  CURRENT_TIMESTAMP ,
 PRIMARY  KEY (  `id`  )  ) ENGINE  = InnoDB  DEFAULT CHARSET  = latin1;


ALTER TABLE `kotak_corp_load_request` ADD `by_corporate_id` INT( 11 ) UNSIGNED NOT NULL AFTER `by_agent_id` ;

ALTER TABLE `kotak_corp_load_request` CHANGE `load_channel` `load_channel` ENUM( 'medi-assist', 'ops', 'corporate' ) NOT NULL ;
ALTER TABLE `kotak_corp_load_request_batch` ADD `by_corporate_id` INT( 11 ) UNSIGNED NOT NULL AFTER `corporate_id` ;

SET @flag_id :=  (select id from `t_flags` where `name` = 'corporate-corp_ratnakar_reports');
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'sampleload', @flag_id, 'Download sample load request file');
SET @privileg_id :=  last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 4, @flag_id, @privileg_id, 1);
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 2, @flag_id, @privileg_id, 1);
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 3, @flag_id, @privileg_id, 1);

SET @flag_id :=  (select id from `t_flags` where `name` = 'corporate-corp_ratnakar_reports');
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'exportsampleload', @flag_id, 'Export ample load request file');
SET @privileg_id :=  last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 4, @flag_id, @privileg_id, 1);
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 2, @flag_id, @privileg_id, 1);
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 3, @flag_id, @privileg_id, 1);

SET @flag_id :=  (select id from `t_flags` where `name` = 'corporate-corp_ratnakar_cardholder');
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'batchstatus', @flag_id, 'Batch status');
SET @privileg_id :=  last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 4, @flag_id, @privileg_id, 1);
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 2, @flag_id, @privileg_id, 1);
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES(NULL, 3, @flag_id, @privileg_id, 1);
