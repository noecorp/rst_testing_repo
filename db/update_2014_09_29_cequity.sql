CREATE TABLE IF NOT EXISTS `tid_master` (
  `tid` varchar(11) NOT NULL,
  `mid` varchar(20) DEFAULT NULL,
  `mcc` int(4) DEFAULT NULL,
  `me_name` varchar(30) NOT NULL,
  `acquire_id` int(6) DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


CREATE TABLE IF NOT EXISTS `bind_purse_tid` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `purse_master_id` int(11) unsigned NOT NULL,
  `tid_code` varchar(10) NOT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;


INSERT INTO `t_flags` (`id`, `name`, `description`, `active_on_dev`, `active_on_prod`) VALUES (NULL, 'corporate-corp_ratnakar_tid', 'CEquity TID Controller', 1, 0);
SET @flag_id = last_insert_id();
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'uploadtid', @flag_id, 'Upload TID File');
SET @priv_id = last_insert_id();
SET @product_id := (SELECT id FROM `t_products` WHERE unicode='921');
INSERT INTO `t_corporate_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES
(null, @product_id, @flag_id, @priv_id, 1);


SET @flag_id = (SELECT id from `t_flags` WHERE name='corporate-corp_ratnakar_tid');
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'uploadtid', @flag_id, 'Upload TID File');
SET @priv_id = last_insert_id();
SET @product_id := (SELECT id FROM `t_products` WHERE unicode='921');
INSERT INTO `t_corporate_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES
(null, @product_id, @flag_id, @priv_id, 1);

SET @flag_id = (SELECT id from `t_flags` WHERE name='corporate-corp_ratnakar_tid');
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'changestatus', @flag_id, 'Change Status');
SET @priv_id = last_insert_id();
SET @product_id := (SELECT id FROM `t_products` WHERE unicode='921');
INSERT INTO `t_corporate_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES
(null, @product_id, @flag_id, @priv_id, 1);



