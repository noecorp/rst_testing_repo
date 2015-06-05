DROP TABLE IF EXISTS `t_files`;
CREATE TABLE `t_files` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `label` varchar(30) COLLATE utf8_unicode_ci DEFAULT '0',
  `file_name` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `date_start` date NOT NULL DEFAULT '0000-00-00',
  `date_end` date NOT NULL DEFAULT '0000-00-00',
  `status` enum('active','inactive') COLLATE utf8_unicode_ci DEFAULT 'active',
  `comment` varchar(255) NOT NULL DEFAULT '',
  `date_created` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;


ALTER TABLE `kotak_corp_cardholders`
ADD COLUMN `date_crn_update`  datetime NULL AFTER `date_failed`;

INSERT INTO `object_relation_types` VALUES ('3', 'KOTAK_AUTHORIZED_APPLICATION', 'Kotak Authorized Application');
