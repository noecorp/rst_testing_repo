INSERT INTO `t_flags` (`id`, `name`, `description`, `active_on_dev`) VALUES ( NULL, 'agent-linkedagents', 'Linked agents', '1')

ALTER TABLE `purse_master`
ADD COLUMN `load_channel`  enum('medi-assist','ops') NULL DEFAULT NULL AFTER `max_balance`,
ADD COLUMN `load_validity_day`  tinyint(4) UNSIGNED NOT NULL AFTER `load_channel`,
ADD COLUMN `load_validity_hr`  tinyint(4) UNSIGNED NOT NULL AFTER `load_validity_day`,
ADD COLUMN `load_validity_min`  tinyint(4) UNSIGNED NOT NULL AFTER `load_validity_hr`,
ADD COLUMN `load_min`  int(11) UNSIGNED NOT NULL AFTER `load_validity_min`,
ADD COLUMN `load_max`  int(11) UNSIGNED NOT NULL AFTER `load_min`,
ADD COLUMN `load_max_cnt_daily`  int(11) UNSIGNED NOT NULL AFTER `load_max`,
ADD COLUMN `load_max_val_daily`  int(11) UNSIGNED NOT NULL AFTER `load_max_cnt_daily`,
ADD COLUMN `load_max_cnt_monthly`  int(11) UNSIGNED NOT NULL AFTER `load_max_val_daily`,
ADD COLUMN `load_max_val_monthly`  int(11) UNSIGNED NOT NULL AFTER `load_max_cnt_monthly`,
ADD COLUMN `load_max_cnt_yearly`  int(11) UNSIGNED NOT NULL AFTER `load_max_val_monthly`,
ADD COLUMN `load_max_val_yearly`  int(11) UNSIGNED NOT NULL AFTER `load_max_cnt_yearly`;

ALTER TABLE `purse_master`
ADD COLUMN `txn_restriction_type`  enum('mcc','tid') NULL DEFAULT NULL AFTER `load_max_val_yearly`,
ADD COLUMN `txn_upload_list`  enum('yes','no') NOT NULL DEFAULT 'no' AFTER `txn_restriction_type`,
ADD COLUMN `txn_min`  int(11) UNSIGNED NOT NULL AFTER `txn_upload_list`,
ADD COLUMN `txn_max`  int(11) UNSIGNED NOT NULL AFTER `txn_min`,
ADD COLUMN `txn_max_cnt_daily`  int(11) UNSIGNED NOT NULL AFTER `txn_max`,
ADD COLUMN `txn_max_val_daily`  int(11) UNSIGNED NOT NULL AFTER `txn_max_cnt_daily`,
ADD COLUMN `txn_max_cnt_monthly`  int(11) UNSIGNED NOT NULL AFTER `txn_max_val_daily`,
ADD COLUMN `txn_max_val_monthly`  int(11) UNSIGNED NOT NULL AFTER `txn_max_cnt_monthly`,
ADD COLUMN `txn_max_cnt_yearly`  int(11) UNSIGNED NOT NULL AFTER `txn_max_val_monthly`,
ADD COLUMN `txn_max_val_yearly`  int(11) UNSIGNED NOT NULL AFTER `txn_max_cnt_yearly`,
ADD COLUMN `date_start`  date NOT NULL AFTER `txn_max_val_yearly`;

ALTER TABLE `purse_master`
DROP COLUMN `initial_balance`,
MODIFY COLUMN `max_balance`  decimal(11,2) NOT NULL AFTER `description`;


UPDATE purse_master SET load_channel = 'medi-assist', load_validity_hr = 2, txn_restriction_type = 'mcc', date_start = NOW() WHERE code = 'RCI310';

UPDATE purse_master SET load_channel = 'ops', txn_restriction_type = 'mcc', date_start = NOW() WHERE code = 'RCH310';

ALTER TABLE `purse_master`
ADD COLUMN `by_ops_id`  int(11) UNSIGNED NOT NULL AFTER `date_updated`;


CREATE TABLE `log_purse_master` (
  `purse_master_id` int(11) unsigned NOT NULL,
  `bank_id` int(11) unsigned NOT NULL,
  `product_id` int(11) unsigned NOT NULL,
  `code` char(6) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` varchar(255) NOT NULL,
  `max_balance` decimal(11,2) NOT NULL,
  `load_channel` enum('medi-assist','ops') DEFAULT NULL,
  `load_validity_day` tinyint(4) unsigned NOT NULL,
  `load_validity_hr` tinyint(4) unsigned NOT NULL,
  `load_validity_min` tinyint(4) unsigned NOT NULL,
  `load_min` int(11) unsigned NOT NULL,
  `load_max` int(11) unsigned NOT NULL,
  `load_max_cnt_daily` int(11) unsigned NOT NULL,
  `load_max_val_daily` int(11) unsigned NOT NULL,
  `load_max_cnt_monthly` int(11) unsigned NOT NULL,
  `load_max_val_monthly` int(11) unsigned NOT NULL,
  `load_max_cnt_yearly` int(11) unsigned NOT NULL,
  `load_max_val_yearly` int(11) unsigned NOT NULL,
  `txn_restriction_type` enum('mcc','tid') DEFAULT NULL,
  `txn_upload_list` enum('yes','no') NOT NULL DEFAULT 'no',
  `txn_min` int(11) unsigned NOT NULL,
  `txn_max` int(11) unsigned NOT NULL,
  `txn_max_cnt_daily` int(11) unsigned NOT NULL,
  `txn_max_val_daily` int(11) unsigned NOT NULL,
  `txn_max_cnt_monthly` int(11) unsigned NOT NULL,
  `txn_max_val_monthly` int(11) unsigned NOT NULL,
  `txn_max_cnt_yearly` int(11) unsigned NOT NULL,
  `txn_max_val_yearly` int(11) unsigned NOT NULL,
  `date_start` date NOT NULL,
  `date_end` date NOT NULL,
  `by_ops_id` int(11) unsigned NOT NULL,
  `date_updated` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=latin1 ;


ALTER TABLE `rat_corp_cardholders`  DROP `passport_expiry`,  DROP `pan_number`;

