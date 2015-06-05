INSERT INTO `t_cron` (`id`, `name`, `description`, `file_name`, `status`, `status_cron`, `date_updated`) VALUES ('26', 'BOI NSDC Account activation', 'BOI NSDC Account activation', '710Activation.php', 'active', 'completed', CURRENT_TIMESTAMP);

ALTER TABLE `boi_corp_cardholders` CHANGE `status` `status` ENUM( 'active', 'inactive', 'pending', 'ecs_failed', 'blocked', 'activated' ) NOT NULL DEFAULT 'pending';

ALTER TABLE `boi_delivery_file_master` CHANGE `delivery_status` `delivery_status` ENUM( 'delivered', 'undelivered', 'approved', 'rejected' ) NOT NULL;