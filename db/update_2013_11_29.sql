DROP TABLE IF EXISTS `object_relation_types`;
CREATE TABLE `object_relation_types` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `label` varchar(32) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `description` varchar(64) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `object_relations`;
CREATE TABLE `object_relations` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `from_object_id` int(11) unsigned NOT NULL DEFAULT '0',
  `to_object_id` int(11) unsigned NOT NULL DEFAULT '0',
  `object_relation_type_id` int(11) unsigned NOT NULL DEFAULT '0',
  `date_start` date DEFAULT '0000-00-00',
  `date_end` date DEFAULT '0000-00-00',
  `status` enum('active','inactive') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'active',
  `comment` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `object_relations_ibfk_1` (`object_relation_type_id`),
  CONSTRAINT `object_relations_ibfk_1` FOREIGN KEY (`object_relation_type_id`) REFERENCES `object_relation_types` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;