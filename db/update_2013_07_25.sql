SET @flag_id := (SELECT id FROM `t_flags` WHERE name='agent-corp_ratnakar_cardholder');
INSERT INTO `t_privileges` (`name`, `flag_id`, `description`) VALUES ('inactive', @flag_id, 'Deactivate Corporate Cardholder');
SET @privilege_id = last_insert_id(); 
SET @product_id := (SELECT id FROM `t_products` WHERE name='MEDI ASSIST CARD');
INSERT INTO `t_product_privileges` (`product_id`, `flag_id`, `privilege_id`, `allow`) VALUES
(@product_id, @flag_id, @privilege_id, 1);


DROP TABLE IF EXISTS `log_master`;
CREATE TABLE `log_master` (
  `date_stamped` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `by_id` int(11) unsigned DEFAULT NULL,
  `by_whom` enum('customer','bank','corporate','agent','helpdesk','ops') DEFAULT NULL,
  `functionality` varchar(100) DEFAULT NULL,
  `session_id` varchar(100) DEFAULT NULL,
  `table_name` varchar(100) DEFAULT NULL,
  `txt_old` text,
  `txt_new` text,
  `remarks` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
