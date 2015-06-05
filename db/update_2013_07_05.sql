 CREATE  TABLE `log_corporate_master` (  `corporate_id` int( 11  )  unsigned NOT  NULL,
 `ecs_corp_id` int( 11  )  unsigned NOT  NULL ,
 `name` varchar( 100  )  NOT  NULL ,
 `address` varchar( 255  )  NOT  NULL ,
 `city` varchar( 100  )  NOT  NULL ,
 `state` varchar( 100  )  NOT  NULL ,
 `pincode` varchar( 10  )  NOT  NULL ,
 `contact_number` varchar( 100  )  NOT  NULL ,
 `contact_email` varchar( 100  )  NOT  NULL ,
 `by_ops_id` int( 11  )  unsigned NOT  NULL ,
 `ip` varchar( 16  )  NOT  NULL ,
 `date_created` timestamp NULL  DEFAULT NULL ,
 `date_updated` timestamp NOT  NULL  DEFAULT CURRENT_TIMESTAMP  ON  UPDATE  CURRENT_TIMESTAMP ,
 `status` enum(  'active',  'inactive'  )  NOT  NULL DEFAULT  'active' ) ENGINE  = InnoDB  DEFAULT CHARSET  = latin1;

SET @flag_id := (SELECT id FROM `t_flags` WHERE name='agent-hic_ratnakar_hospital');
SET @product_id := (SELECT id FROM `t_products` WHERE name='MEDI ASSIST CARD');

INSERT INTO `t_privileges` (`name`, `flag_id`, `description`) VALUES ('edit', @flag_id, 'Edit Hospital');
SET @privilege_id = last_insert_id();
INSERT INTO `t_product_privileges` (`product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (@product_id, @flag_id, @privilege_id, 1);

