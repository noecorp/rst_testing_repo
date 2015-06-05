DROP TABLE IF EXISTS `customer_crn`;

ALTER TABLE `crn_master`
MODIFY COLUMN `card_pack_id`  varchar(30) NOT NULL AFTER `card_number`;

DROP TABLE IF EXISTS `customer_track`;
CREATE TABLE `customer_track` (
  `product_id` int(11) unsigned NOT NULL,
  `customer_id` int(11) unsigned NOT NULL,
  `info` varchar(100) NOT NULL,
  `flag` tinyint(3) unsigned NOT NULL,
  `date_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO `object_relation_types` VALUES ('4', 'TYPE_A', 'Mob value');
INSERT INTO `object_relation_types` VALUES ('5', 'TYPE_B', 'Card');
INSERT INTO `object_relation_types` VALUES ('6', 'TYPE_C', 'Pack');
INSERT INTO `object_relation_types` VALUES ('7', 'TYPE_D', 'CRN');
INSERT INTO `object_relation_types` VALUES ('8', 'TYPE_E', 'Mem value');
INSERT INTO `object_relation_types` VALUES ('9', 'TYPE_F', 'Email');
INSERT INTO `object_relation_types` VALUES ('10', 'TYPE_G', 'Name');
INSERT INTO `object_relation_types` VALUES ('11', 'TYPE_H', 'OTP');

ALTER TABLE `customer_track` ADD `id` INT( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST;