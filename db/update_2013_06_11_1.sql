-- ----------------------------
-- Table structure for `t_cron_schedule`
-- ----------------------------
DROP TABLE IF EXISTS `t_cron_schedule`;
CREATE TABLE `t_cron_schedule` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `cron_id` int(11) unsigned NOT NULL,
  `schedule_day` tinyint(1) NOT NULL COMMENT 'Day to run cron - 1-7 (Mon -Sun)',
  `schedule_time` time NOT NULL COMMENT 'time to run cron',
  `status` enum('active','inactive') DEFAULT 'active',
  `date_added` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of t_cron_schedule
-- ----------------------------
INSERT INTO `t_cron_schedule` VALUES ('1', '14', '1', '09:30:00', 'active', '2013-06-11 16:51:43');
INSERT INTO `t_cron_schedule` VALUES ('2', '14', '1', '11:30:00', 'active', '2013-06-11 16:51:43');
INSERT INTO `t_cron_schedule` VALUES ('3', '14', '1', '13:30:00', 'active', '2013-06-11 16:51:43');
INSERT INTO `t_cron_schedule` VALUES ('4', '14', '1', '15:30:00', 'active', '2013-06-11 16:51:43');
INSERT INTO `t_cron_schedule` VALUES ('5', '14', '1', '17:30:00', 'active', '2013-06-11 16:51:43');
INSERT INTO `t_cron_schedule` VALUES ('6', '14', '2', '09:30:00', 'active', '2013-06-11 16:51:43');
INSERT INTO `t_cron_schedule` VALUES ('7', '14', '2', '11:30:00', 'active', '2013-06-11 16:51:43');
INSERT INTO `t_cron_schedule` VALUES ('8', '14', '2', '13:30:00', 'active', '2013-06-11 16:51:43');
INSERT INTO `t_cron_schedule` VALUES ('9', '14', '2', '15:30:00', 'active', '2013-06-11 16:51:43');
INSERT INTO `t_cron_schedule` VALUES ('10', '14', '2', '17:30:00', 'active', '2013-06-11 16:51:43');
INSERT INTO `t_cron_schedule` VALUES ('11', '14', '3', '09:30:00', 'active', '2013-06-11 16:51:43');
INSERT INTO `t_cron_schedule` VALUES ('12', '14', '3', '11:30:00', 'active', '2013-06-11 16:51:43');
INSERT INTO `t_cron_schedule` VALUES ('13', '14', '3', '13:30:00', 'active', '2013-06-11 16:51:43');
INSERT INTO `t_cron_schedule` VALUES ('14', '14', '3', '15:30:00', 'active', '2013-06-11 16:51:43');
INSERT INTO `t_cron_schedule` VALUES ('15', '14', '3', '17:30:00', 'active', '2013-06-11 16:51:43');
INSERT INTO `t_cron_schedule` VALUES ('16', '14', '4', '09:30:00', 'active', '2013-06-11 16:51:43');
INSERT INTO `t_cron_schedule` VALUES ('17', '14', '4', '11:30:00', 'active', '2013-06-11 16:51:43');
INSERT INTO `t_cron_schedule` VALUES ('18', '14', '4', '13:30:00', 'active', '2013-06-11 16:51:43');
INSERT INTO `t_cron_schedule` VALUES ('19', '14', '4', '15:30:00', 'active', '2013-06-11 16:51:43');
INSERT INTO `t_cron_schedule` VALUES ('20', '14', '4', '17:30:00', 'active', '2013-06-11 16:51:43');
INSERT INTO `t_cron_schedule` VALUES ('21', '14', '5', '09:30:00', 'active', '2013-06-11 16:51:43');
INSERT INTO `t_cron_schedule` VALUES ('22', '14', '5', '11:30:00', 'active', '2013-06-11 16:51:43');
INSERT INTO `t_cron_schedule` VALUES ('23', '14', '5', '13:30:00', 'active', '2013-06-11 16:51:43');
INSERT INTO `t_cron_schedule` VALUES ('24', '14', '5', '15:30:00', 'active', '2013-06-11 16:51:43');
INSERT INTO `t_cron_schedule` VALUES ('25', '14', '5', '17:30:00', 'active', '2013-06-11 16:51:43');
INSERT INTO `t_cron_schedule` VALUES ('26', '14', '6', '09:30:00', 'active', '2013-06-11 16:51:43');
INSERT INTO `t_cron_schedule` VALUES ('27', '14', '6', '11:30:00', 'active', '2013-06-11 16:51:43');