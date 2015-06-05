INSERT INTO `t_cron` (`id`, `name`, `description`, `file_name`, `status`, `status_cron`, `date_updated`) VALUES (90, 'Revert incomplete transfer', 'Credit incomplete wallet transfer', 'RevertIncTransfer.php', 'active', 'completed', NOW());

CREATE TABLE IF NOT EXISTS `wallet_credit_info` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `wallet_transfer_id` int(11) unsigned NOT NULL,
  `product_id` int(11) NOT NULL,
  `txn_code` int(11) unsigned NOT NULL,
  `rat_customer_id` int(11) unsigned NOT NULL,
  `customer_purse_id` int(11) unsigned NOT NULL,
  `amount` decimal(11,2) NOT NULL,
  `txn_type` char(4) NOT NULL,
  `failed_reason` varchar(255) DEFAULT NULL,
  `date_ecs` datetime DEFAULT NULL,
  `status` enum('success','failed') NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

ALTER TABLE `rat_wallet_transfer` ADD `date_reversal` DATETIME NULL DEFAULT NULL AFTER `date_updated`;
