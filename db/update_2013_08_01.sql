CREATE TABLE `purse_master` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `bank_id` int(11) unsigned NOT NULL,
  `product_id` int(11) unsigned NOT NULL,
  `code` char(6) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` varchar(255) NOT NULL,
  `initial_balance` decimal(11,2) NOT NULL,
  `max_balance` decimal(11,2) DEFAULT NULL,
  `date_created` timestamp NULL DEFAULT NULL,
  `date_updated` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1 COMMENT='This is product-purse configuration';


SET @bank_id_val := (SELECT id FROM `t_bank` WHERE unicode = '300' AND status = 'active');

SET @product_id_val := (SELECT id FROM `t_products` WHERE bank_id = @bank_id_val AND unicode = '310' AND status = 'active');

INSERT INTO `purse_master` (`bank_id`, `product_id`, `code`, `name`, `description`, `initial_balance`, `max_balance`, `date_created`, `status`) 
VALUES (@bank_id_val, @product_id_val, 'RCI310', 'Purse INS', 'Corporate Purse for Insurance', '0.0', '10000000.00', NOW(), 'active');

INSERT INTO `purse_master` (`bank_id`, `product_id`, `code`, `name`, `description`, `initial_balance`, `max_balance`, `date_created`, `status`)  
VALUES (@bank_id_val, @product_id_val, 'RCH310', 'Purse HR', 'Corporate Purse for HR', '0.0', '10000000.00', NOW(), 'active');
