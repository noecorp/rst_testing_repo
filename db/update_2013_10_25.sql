SET @flag_id := (SELECT id FROM `t_flags` WHERE name = 'operation-corp_ratnakar_cardload');

INSERT INTO `t_privileges` (`id` ,`name` ,`flag_id` ,`description`)
VALUES (NULL , 'walletstatus', @flag_id, 'Wallet status report');

DROP TABLE IF EXISTS `crn_master`;
CREATE TABLE `crn_master` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `card_number` varchar(16) NOT NULL,
  `card_pack_id` varchar(20) NOT NULL,
  `member_id` varchar(20) NOT NULL,
  `status` enum('free','blocked','duplicate','temp','used') NOT NULL DEFAULT 'free',
  `file` varchar(50) DEFAULT NULL,
  `by_ops_id` int(11) unsigned DEFAULT NULL,
  `date_created` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


UPDATE t_transaction_type SET typecode = 'RCCL' WHERE typecode = 'RCLD' LIMIT 1;

INSERT INTO t_transaction_type (typecode, name, status, is_comm) VALUES ('RCML', 'Ratnakar Medi-Assist CardLoad', 'active' , 'no');

ALTER TABLE `rat_corp_load_request`
ADD COLUMN `failed_reason`  varchar(200) NOT NULL AFTER `date_load`;

ALTER TABLE `rat_corp_load_request`
ADD COLUMN `date_failed`  datetime NOT NULL AFTER `date_load`;


SET @flag_id := (SELECT id FROM `t_flags` WHERE name = 'operation-corp_ratnakar_cardholder');

INSERT INTO `t_privileges` (`id` ,`name` ,`flag_id` ,`description`)
VALUES (NULL , 'uploadcrn', @flag_id, 'Upload MediAssist CRN');

