UPDATE `t_cron` SET `file_name` = 'UpdateBoiCustPurseClosingBalance.php', `name` = 'Boi Customer Purse Closing Balance', `description` = 'Cron will update closing balance for boi customer purse' WHERE `t_cron`.`id` =30;

INSERT INTO `t_cron` (`id`, `name`, `description`, `file_name`, `status`, `status_cron`, `date_updated`) VALUES ('33', 'Ratnakar Customer Purse Closing Balance', 'Cron will update closing balance for rat customer purse', 'UpdateRatCustPurseClosingBalance.php', 'active', 'completed', CURRENT_TIMESTAMP);
INSERT INTO `t_cron` (`id`, `name`, `description`, `file_name`, `status`, `status_cron`, `date_updated`) VALUES ('34', 'Kotak Customer Purse Closing Balance', 'Cron will update closing balance for kotak customer purse', 'UpdateKotakCustPurseClosingBalance.php', 'active', 'completed', CURRENT_TIMESTAMP);

ALTER TABLE `boi_corp_load_request`
MODIFY COLUMN `id`  int(11) UNSIGNED NULL AUTO_INCREMENT FIRST ,
ADD COLUMN `amount_available`  decimal(11,2) NOT NULL AFTER `amount`,
ADD COLUMN `amount_used`  decimal(11,2) NOT NULL AFTER `amount_available`,
ADD COLUMN `amount_cutoff`  decimal(11,2) NOT NULL AFTER `amount_used`;

ALTER TABLE `kotak_corp_load_request`
MODIFY COLUMN `id`  int(11) UNSIGNED NULL AUTO_INCREMENT FIRST ,
ADD COLUMN `amount_available`  decimal(11,2) NOT NULL AFTER `amount`,
ADD COLUMN `amount_used`  decimal(11,2) NOT NULL AFTER `amount_available`,
ADD COLUMN `amount_cutoff`  decimal(11,2) NOT NULL AFTER `amount_used`;


ALTER TABLE `rat_corp_load_request`
MODIFY COLUMN `id`  int(11) UNSIGNED NULL AUTO_INCREMENT FIRST ,
ADD COLUMN `amount_available`  decimal(11,2) NOT NULL AFTER `amount`,
ADD COLUMN `amount_used`  decimal(11,2) NOT NULL AFTER `amount_available`,
ADD COLUMN `amount_cutoff`  decimal(11,2) NOT NULL AFTER `amount_used`;


DROP TABLE IF EXISTS `boi_corp_load_request_detail`;
CREATE TABLE `boi_corp_load_request_detail` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int(11) unsigned NOT NULL,
  `load_request_id` int(11) unsigned NOT NULL,
  `txn_processing_id` int(11) unsigned NOT NULL,
  `amount` decimal(14,2) NOT NULL,
  `txn_code` int(11) unsigned NOT NULL,
  `txn_type` char(4) DEFAULT NULL,
  `date_created` datetime DEFAULT NULL,
  `date_reversal` datetime NOT NULL,
  `date_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `status` enum('pending','failed','success') NOT NULL DEFAULT 'pending',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `rat_corp_load_request_detail`;
CREATE TABLE `rat_corp_load_request_detail` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int(11) unsigned NOT NULL,
  `load_request_id` int(11) unsigned NOT NULL,
  `txn_processing_id` int(11) unsigned NOT NULL,
  `amount` decimal(14,2) NOT NULL,
  `txn_code` int(11) unsigned NOT NULL,
  `txn_type` char(4) DEFAULT NULL,
  `date_created` datetime DEFAULT NULL,
  `date_reversal` datetime NOT NULL,
  `date_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `status` enum('pending','failed','success') NOT NULL DEFAULT 'pending',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



ALTER TABLE `rat_corp_load_request`
MODIFY COLUMN `status`  enum('pending','loaded','failed','cutoff','blocked','completed','incomplete','started','debited') NOT NULL DEFAULT 'pending' AFTER `date_updated`;

ALTER TABLE `kotak_corp_load_request`
MODIFY COLUMN `status`  enum('pending','loaded','failed','cutoff','blocked','completed','incomplete','started','debited') NOT NULL DEFAULT 'pending' AFTER `date_updated`;

ALTER TABLE `boi_corp_load_request`
MODIFY COLUMN `status`  enum('pending','loaded','failed','cutoff','blocked','completed','incomplete','started','debited') NOT NULL DEFAULT 'pending' AFTER `date_updated`;


UPDATE boi_corp_load_request SET amount_available = amount WHERE status = 'loaded' AND mode = 'cr';
UPDATE rat_corp_load_request SET amount_available = amount WHERE status = 'loaded'  AND mode = 'cr';
UPDATE kotak_corp_load_request SET amount_available = amount WHERE status = 'loaded'  AND mode = 'cr';

UPDATE boi_corp_load_request SET amount_available = 0, amount_cutoff = amount, status = 'loaded' WHERE status = 'cutoff' AND mode = 'cr';
UPDATE rat_corp_load_request SET amount_available = 0, amount_cutoff = amount, status = 'loaded' WHERE status = 'cutoff' AND mode = 'cr';
UPDATE kotak_corp_load_request SET amount_available = 0, amount_cutoff = amount, status = 'loaded' WHERE status = 'cutoff' AND mode = 'cr';
