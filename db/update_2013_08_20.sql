DROP TABLE IF EXISTS `purse_master`;
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
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1 COMMENT='This is product-purse configuration';

-- ----------------------------
-- Records of purse_master
-- ----------------------------
INSERT INTO `purse_master` VALUES ('1', '17', '40', 'RCI310', 'Purse INS', 'Corporate Purse for Insurance', '0.00', '10000000.00', '2013-08-01 18:34:18', '2013-08-02 11:42:36', 'active');
INSERT INTO `purse_master` VALUES ('2', '17', '40', 'RCH310', 'Purse HR', 'Corporate Purse for HR', '0.00', '10000000.00', '2013-08-01 18:34:18', '2013-08-02 11:42:37', 'active');


ALTER TABLE `rat_corp_insurance_claim` ADD `medi_assist_id` VARCHAR( 10 ) NOT NULL AFTER `cardholder_id`;
ALTER TABLE `rat_corp_insurance_claim` ADD `terminal_id_code` BIGINT( 16 ) NOT NULL AFTER `hospital_id_code` ,
ADD `hospital_mcc` VARCHAR( 10 ) NOT NULL AFTER `terminal_id_code`;

ALTER TABLE `rat_corp_insurance_claim` CHANGE `terminal_id_code` `terminal_id_code` VARCHAR( 50 ) NOT NULL;




--------------------------------------------------------

-- ----------------------------
-- Table structure for `rat_corp_crn`
-- ----------------------------
DROP TABLE IF EXISTS `rat_corp_crn`;
CREATE TABLE `rat_corp_crn` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `card_number` varchar(16) NOT NULL,
  `card_holder_name` varchar(100) DEFAULT NULL,
  `member_id` varchar(30) DEFAULT NULL,
  `valid_thru` varchar(20) DEFAULT NULL,
  `valid_from` varchar(20) DEFAULT NULL,
  `account_no` varchar(20) DEFAULT NULL,
  `address1` varchar(50) DEFAULT NULL,
  `address2` varchar(50) DEFAULT NULL,
  `city` varchar(50) DEFAULT NULL,
  `state` varchar(50) DEFAULT NULL,
  `pincode` varchar(10) DEFAULT NULL,
  `phone_no` varchar(20) DEFAULT NULL,
  `mobile_no` varchar(15) DEFAULT NULL,
  `card_pack_id` varchar(20) DEFAULT 'free',
  `status` enum('free','block') DEFAULT 'free',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



---------------------------------------------------------------


INSERT INTO `t_unicode` VALUES (NULL, '300', '310', null, '4780745100000593', 'free', '0000-00-00 00:00:00', '2013-08-20 14:24:50');
INSERT INTO `t_unicode` VALUES (NULL, '300', '310', null, '4780745100000601', 'free', '0000-00-00 00:00:00', '2013-08-20 14:26:27');
INSERT INTO `t_unicode` VALUES (NULL, '300', '310', null, '4780745100000619', 'free', '0000-00-00 00:00:00', '2013-08-20 14:26:27');
INSERT INTO `t_unicode` VALUES (NULL, '300', '310', null, '4780745100000627', 'free', '0000-00-00 00:00:00', '2013-08-20 14:26:27');
INSERT INTO `t_unicode` VALUES (NULL, '300', '310', null, '4780745100000635', 'free', '0000-00-00 00:00:00', '2013-08-20 14:26:27');
INSERT INTO `t_unicode` VALUES (NULL, '300', '310', null, '4780745100000643', 'free', '0000-00-00 00:00:00', '2013-08-20 14:26:27');
INSERT INTO `t_unicode` VALUES (NULL, '300', '310', null, '4780745100000650', 'free', '0000-00-00 00:00:00', '2013-08-20 14:26:27');
INSERT INTO `t_unicode` VALUES (NULL, '300', '310', null, '4780745100000668', 'free', '0000-00-00 00:00:00', '2013-08-20 14:26:27');
INSERT INTO `t_unicode` VALUES (NULL, '300', '310', null, '4780745100000676', 'free', '0000-00-00 00:00:00', '2013-08-20 14:26:27');
INSERT INTO `t_unicode` VALUES (NULL , '300', '310', null, '4780745100000684', 'free', '0000-00-00 00:00:00', '2013-08-20 14:26:27');
INSERT INTO `t_unicode` VALUES (NULL , '300', '310', null, '4780745100000692', 'free', '0000-00-00 00:00:00', '2013-08-20 14:26:27');
INSERT INTO `t_unicode` VALUES (NULL , '300', '310', null, '4780745100000700', 'free', '0000-00-00 00:00:00', '2013-08-20 14:26:27');
INSERT INTO `t_unicode` VALUES (NULL , '300', '310', null, '4780745100000718', 'free', '0000-00-00 00:00:00', '2013-08-20 14:26:27');
INSERT INTO `t_unicode` VALUES (NULL , '300', '310', null, '4780745100000726', 'free', '0000-00-00 00:00:00', '2013-08-20 14:26:27');
INSERT INTO `t_unicode` VALUES (NULL , '300', '310', null, '4780745100000734', 'free', '0000-00-00 00:00:00', '2013-08-20 14:26:27');
INSERT INTO `t_unicode` VALUES (NULL , '300', '310', null, '4780745100000742', 'free', '0000-00-00 00:00:00', '2013-08-20 14:26:27');
INSERT INTO `t_unicode` VALUES (NULL , '300', '310', null, '4780745100000759', 'free', '0000-00-00 00:00:00', '2013-08-20 14:26:27');
INSERT INTO `t_unicode` VALUES (NULL , '300', '310', null, '4780745100000767', 'free', '0000-00-00 00:00:00', '2013-08-20 14:26:27');
INSERT INTO `t_unicode` VALUES (NULL , '300', '310', null, '4780745100000775', 'free', '0000-00-00 00:00:00', '2013-08-20 14:26:27');
INSERT INTO `t_unicode` VALUES (NULL , '300', '310', null, '4780745100000783', 'free', '0000-00-00 00:00:00', '2013-08-20 14:26:27');
INSERT INTO `t_unicode` VALUES (NULL , '300', '310', null, '4780745100000791', 'free', '0000-00-00 00:00:00', '2013-08-20 14:26:27');
INSERT INTO `t_unicode` VALUES (NULL , '300', '310', null, '4780745100000809', 'free', '0000-00-00 00:00:00', '2013-08-20 14:26:27');
INSERT INTO `t_unicode` VALUES (NULL , '300', '310', null, '4780745100000817', 'free', '0000-00-00 00:00:00', '2013-08-20 14:26:27');
INSERT INTO `t_unicode` VALUES (NULL , '300', '310', null, '4780745100000825', 'free', '0000-00-00 00:00:00', '2013-08-20 14:26:27');
INSERT INTO `t_unicode` VALUES (NULL , '300', '310', null, '4780745100000833', 'free', '0000-00-00 00:00:00', '2013-08-20 14:26:27');
INSERT INTO `t_unicode` VALUES (NULL , '300', '310', null, '4780745100000841', 'free', '0000-00-00 00:00:00', '2013-08-20 14:26:27');
INSERT INTO `t_unicode` VALUES (NULL , '300', '310', null, '4780745100000858', 'free', '0000-00-00 00:00:00', '2013-08-20 14:26:27');
INSERT INTO `t_unicode` VALUES (NULL , '300', '310', null, '4780745100000866', 'free', '0000-00-00 00:00:00', '2013-08-20 14:26:27');
INSERT INTO `t_unicode` VALUES (NULL , '300', '310', null, '4780745100000874', 'free', '0000-00-00 00:00:00', '2013-08-20 14:26:27');
INSERT INTO `t_unicode` VALUES (NULL , '300', '310', null, '4780745100000882', 'free', '0000-00-00 00:00:00', '2013-08-20 14:26:27');
INSERT INTO `t_unicode` VALUES (NULL , '300', '310', null, '4780745100000890', 'free', '0000-00-00 00:00:00', '2013-08-20 14:26:27');
INSERT INTO `t_unicode` VALUES (NULL , '300', '310', null, '4780745100000908', 'free', '0000-00-00 00:00:00', '2013-08-20 14:26:27');
INSERT INTO `t_unicode` VALUES (NULL , '300', '310', null, '4780745100000916', 'free', '0000-00-00 00:00:00', '2013-08-20 14:26:27');
INSERT INTO `t_unicode` VALUES (NULL , '300', '310', null, '4780745100000924', 'free', '0000-00-00 00:00:00', '2013-08-20 14:26:27');
INSERT INTO `t_unicode` VALUES (NULL , '300', '310', null, '4780745100000932', 'free', '0000-00-00 00:00:00', '2013-08-20 14:26:27');
INSERT INTO `t_unicode` VALUES (NULL , '300', '310', null, '4780745100000940', 'free', '0000-00-00 00:00:00', '2013-08-20 14:26:27');
INSERT INTO `t_unicode` VALUES (NULL , '300', '310', null, '4780745100000957', 'free', '0000-00-00 00:00:00', '2013-08-20 14:26:27');
INSERT INTO `t_unicode` VALUES (NULL , '300', '310', null, '4780745100000965', 'free', '0000-00-00 00:00:00', '2013-08-20 14:26:27');
INSERT INTO `t_unicode` VALUES (NULL , '300', '310', null, '4780745100000973', 'free', '0000-00-00 00:00:00', '2013-08-20 14:26:27');
INSERT INTO `t_unicode` VALUES (NULL , '300', '310', null, '4780745100000981', 'free', '0000-00-00 00:00:00', '2013-08-20 14:26:27');
INSERT INTO `t_unicode` VALUES (NULL , '300', '310', null, '4780745100000999', 'free', '0000-00-00 00:00:00', '2013-08-20 14:26:27');
INSERT INTO `t_unicode` VALUES (NULL , '300', '310', null, '4780745100001005', 'free', '0000-00-00 00:00:00', '2013-08-20 14:26:27');
INSERT INTO `t_unicode` VALUES (NULL , '300', '310', null, '4780745100001013', 'free', '0000-00-00 00:00:00', '2013-08-20 14:26:27');
INSERT INTO `t_unicode` VALUES (NULL , '300', '310', null, '4780745100001021', 'free', '0000-00-00 00:00:00', '2013-08-20 14:26:27');
INSERT INTO `t_unicode` VALUES (NULL , '300', '310', null, '4780745100001039', 'free', '0000-00-00 00:00:00', '2013-08-20 14:26:27');
INSERT INTO `t_unicode` VALUES (NULL , '300', '310', null, '4780745100001047', 'free', '0000-00-00 00:00:00', '2013-08-20 14:26:27');
INSERT INTO `t_unicode` VALUES (NULL , '300', '310', null, '4780745100001054', 'free', '0000-00-00 00:00:00', '2013-08-20 14:26:27');
INSERT INTO `t_unicode` VALUES (NULL , '300', '310', null, '4780745100001062', 'free', '0000-00-00 00:00:00', '2013-08-20 14:26:27');
INSERT INTO `t_unicode` VALUES (NULL , '300', '310', null, '4780745100001070', 'free', '0000-00-00 00:00:00', '2013-08-20 14:26:27');
INSERT INTO `t_unicode` VALUES (NULL , '300', '310', null, '4780745100001088', 'free', '0000-00-00 00:00:00', '2013-08-20 14:26:27');
INSERT INTO `t_unicode` VALUES (NULL , '300', '310', null, '4780745100001096', 'free', '0000-00-00 00:00:00', '2013-08-20 14:26:27');
INSERT INTO `t_unicode` VALUES (NULL , '300', '310', null, '4780745100001104', 'free', '0000-00-00 00:00:00', '2013-08-20 14:26:27');
INSERT INTO `t_unicode` VALUES (NULL , '300', '310', null, '4780745100001112', 'free', '0000-00-00 00:00:00', '2013-08-20 14:26:27');
INSERT INTO `t_unicode` VALUES (NULL , '300', '310', null, '4780745100001120', 'free', '0000-00-00 00:00:00', '2013-08-20 14:26:27');
INSERT INTO `t_unicode` VALUES (NULL , '300', '310', null, '4780745100001138', 'free', '0000-00-00 00:00:00', '2013-08-20 14:26:27');
INSERT INTO `t_unicode` VALUES (NULL , '300', '310', null, '4780745100001146', 'free', '0000-00-00 00:00:00', '2013-08-20 14:26:27');
INSERT INTO `t_unicode` VALUES (NULL , '300', '310', null, '4780745100001153', 'free', '0000-00-00 00:00:00', '2013-08-20 14:26:27');
INSERT INTO `t_unicode` VALUES (NULL , '300', '310', null, '4780745100001161', 'free', '0000-00-00 00:00:00', '2013-08-20 14:26:27');
INSERT INTO `t_unicode` VALUES (NULL , '300', '310', null, '4780745100001179', 'free', '0000-00-00 00:00:00', '2013-08-20 14:26:27');
INSERT INTO `t_unicode` VALUES (NULL , '300', '310', null, '4780745100001187', 'free', '0000-00-00 00:00:00', '2013-08-20 14:26:27');
INSERT INTO `t_unicode` VALUES (NULL , '300', '310', null, '4780745100001195', 'free', '0000-00-00 00:00:00', '2013-08-20 14:26:27');
INSERT INTO `t_unicode` VALUES (NULL , '300', '310', null, '4780745100001203', 'free', '0000-00-00 00:00:00', '2013-08-20 14:26:27');
INSERT INTO `t_unicode` VALUES (NULL , '300', '310', null, '4780745100001211', 'free', '0000-00-00 00:00:00', '2013-08-20 14:26:27');
INSERT INTO `t_unicode` VALUES (NULL , '300', '310', null, '4780745100001229', 'free', '0000-00-00 00:00:00', '2013-08-20 14:26:27');
INSERT INTO `t_unicode` VALUES (NULL , '300', '310', null, '4780745100001237', 'free', '0000-00-00 00:00:00', '2013-08-20 14:26:27');
INSERT INTO `t_unicode` VALUES (NULL , '300', '310', null, '4780745100001245', 'free', '0000-00-00 00:00:00', '2013-08-20 14:26:27');
INSERT INTO `t_unicode` VALUES (NULL , '300', '310', null, '4780745100001252', 'free', '0000-00-00 00:00:00', '2013-08-20 14:26:27');
INSERT INTO `t_unicode` VALUES (NULL , '300', '310', null, '4780745100001260', 'free', '0000-00-00 00:00:00', '2013-08-20 14:26:27');
INSERT INTO `t_unicode` VALUES (NULL , '300', '310', null, '4780745100001278', 'free', '0000-00-00 00:00:00', '2013-08-20 14:26:27');
INSERT INTO `t_unicode` VALUES (NULL , '300', '310', null, '4780745100001286', 'free', '0000-00-00 00:00:00', '2013-08-20 14:26:27');
INSERT INTO `t_unicode` VALUES (NULL , '300', '310', null, '4780745100001294', 'free', '0000-00-00 00:00:00', '2013-08-20 14:26:27');
INSERT INTO `t_unicode` VALUES (NULL , '300', '310', null, '4780745100001302', 'free', '0000-00-00 00:00:00', '2013-08-20 14:26:27');
INSERT INTO `t_unicode` VALUES (NULL , '300', '310', null, '4780745100001310', 'free', '0000-00-00 00:00:00', '2013-08-20 14:26:27');
INSERT INTO `t_unicode` VALUES (NULL , '300', '310', null, '4780745100001328', 'free', '0000-00-00 00:00:00', '2013-08-20 14:26:27');
INSERT INTO `t_unicode` VALUES (NULL , '300', '310', null, '4780745100001336', 'free', '0000-00-00 00:00:00', '2013-08-20 14:26:27');
INSERT INTO `t_unicode` VALUES (NULL , '300', '310', null, '4780745100001344', 'free', '0000-00-00 00:00:00', '2013-08-20 14:26:27');
INSERT INTO `t_unicode` VALUES (NULL , '300', '310', null, '4780745100001351', 'free', '0000-00-00 00:00:00', '2013-08-20 14:26:27');
INSERT INTO `t_unicode` VALUES (NULL , '300', '310', null, '4780745100001369', 'free', '0000-00-00 00:00:00', '2013-08-20 14:26:27');
INSERT INTO `t_unicode` VALUES (NULL , '300', '310', null, '4780745100001377', 'free', '0000-00-00 00:00:00', '2013-08-20 14:26:27');
INSERT INTO `t_unicode` VALUES (NULL , '300', '310', null, '4780745100001385', 'free', '0000-00-00 00:00:00', '2013-08-20 14:26:27');
INSERT INTO `t_unicode` VALUES (NULL , '300', '310', null, '4780745100001393', 'free', '0000-00-00 00:00:00', '2013-08-20 14:26:27');
INSERT INTO `t_unicode` VALUES (NULL , '300', '310', null, '4780745100001401', 'free', '0000-00-00 00:00:00', '2013-08-20 14:26:27');
INSERT INTO `t_unicode` VALUES (NULL , '300', '310', null, '4780745100001419', 'free', '0000-00-00 00:00:00', '2013-08-20 14:26:27');
INSERT INTO `t_unicode` VALUES (NULL , '300', '310', null, '4780745100001427', 'free', '0000-00-00 00:00:00', '2013-08-20 14:26:27');
INSERT INTO `t_unicode` VALUES (NULL , '300', '310', null, '4780745100001435', 'free', '0000-00-00 00:00:00', '2013-08-20 14:26:27');
INSERT INTO `t_unicode` VALUES (NULL , '300', '310', null, '4780745100001443', 'free', '0000-00-00 00:00:00', '2013-08-20 14:26:27');
INSERT INTO `t_unicode` VALUES (NULL , '300', '310', null, '4780745100001450', 'free', '0000-00-00 00:00:00', '2013-08-20 14:26:27');
INSERT INTO `t_unicode` VALUES (NULL , '300', '310', null, '4780745100001468', 'free', '0000-00-00 00:00:00', '2013-08-20 14:26:27');
INSERT INTO `t_unicode` VALUES (NULL , '300', '310', null, '4780745100001476', 'free', '0000-00-00 00:00:00', '2013-08-20 14:26:27');
INSERT INTO `t_unicode` VALUES (NULL , '300', '310', null, '4780745100001484', 'free', '0000-00-00 00:00:00', '2013-08-20 14:26:27');
INSERT INTO `t_unicode` VALUES (NULL , '300', '310', null, '4780745100001492', 'free', '0000-00-00 00:00:00', '2013-08-20 14:26:27');
INSERT INTO `t_unicode` VALUES (NULL , '300', '310', null, '4780745100001500', 'free', '0000-00-00 00:00:00', '2013-08-20 14:26:27');
INSERT INTO `t_unicode` VALUES (NULL , '300', '310', null, '4780745100001518', 'free', '0000-00-00 00:00:00', '2013-08-20 14:26:27');
INSERT INTO `t_unicode` VALUES (NULL , '300', '310', null, '4780745100001526', 'free', '0000-00-00 00:00:00', '2013-08-20 14:26:27');
INSERT INTO `t_unicode` VALUES (NULL , '300', '310', null, '4780745100001534', 'free', '0000-00-00 00:00:00', '2013-08-20 14:26:27');
INSERT INTO `t_unicode` VALUES (NULL , '300', '310', null, '4780745100001542', 'free', '0000-00-00 00:00:00', '2013-08-20 14:26:27');
INSERT INTO `t_unicode` VALUES (NULL , '300', '310', null, '4780745100001559', 'free', '0000-00-00 00:00:00', '2013-08-20 14:26:27');
INSERT INTO `t_unicode` VALUES (NULL , '300', '310', null, '4780745100001567', 'free', '0000-00-00 00:00:00', '2013-08-20 14:26:27');
INSERT INTO `t_unicode` VALUES (NULL , '300', '310', null, '4780745100001575', 'free', '0000-00-00 00:00:00', '2013-08-20 14:26:27');
INSERT INTO `t_unicode` VALUES (NULL , '300', '310', null, '4780745100001583', 'free', '0000-00-00 00:00:00', '2013-08-20 14:26:27');


