/*
Navicat MySQL Data Transfer

Source Server         : localhost
Source Server Version : 50524
Source Host           : localhost:3306
Source Database       : zf-rnd

Target Server Type    : MYSQL
Target Server Version : 50524
File Encoding         : 65001

Date: 2012-10-29 16:54:52
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for `t_agent_areas`
-- ----------------------------
DROP TABLE IF EXISTS `t_agent_areas`;
CREATE TABLE `t_agent_areas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `agent_id` int(11) NOT NULL,
  `village` varchar(50) CHARACTER SET utf8 NOT NULL,
  `taluka` varchar(50) CHARACTER SET utf8 NOT NULL,
  `state` int(10) NOT NULL,
  `pincode` int(10) NOT NULL,
  `date_created` datetime NOT NULL,
  `date_updated` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of t_agent_areas
-- ----------------------------

-- ----------------------------
-- Table structure for `t_agent_details`
-- ----------------------------
DROP TABLE IF EXISTS `t_agent_details`;
CREATE TABLE `t_agent_details` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `agent_id` int(11) NOT NULL,
  `first_name` varchar(20) CHARACTER SET utf8 NOT NULL,
  `middle_name` varchar(20) CHARACTER SET utf8 NOT NULL,
  `last_name` varchar(20) CHARACTER SET utf8 NOT NULL,
  `father_first_name` varchar(20) CHARACTER SET utf8 NOT NULL,
  `father_middle_name` varchar(20) CHARACTER SET utf8 NOT NULL,
  `father_last_name` varchar(20) CHARACTER SET utf8 NOT NULL,
  `spouse_first_name` varchar(20) CHARACTER SET utf8 NOT NULL,
  `spouse_middle_name` varchar(20) CHARACTER SET utf8 NOT NULL,
  `spouse_last_name` varchar(20) CHARACTER SET utf8 NOT NULL,
  `mother_maiden_name` varchar(20) CHARACTER SET utf8 NOT NULL,
  `eatab_name` varchar(80) CHARACTER SET utf8 NOT NULL,
  `home` varchar(100) CHARACTER SET utf8 NOT NULL,
  `office` varchar(80) CHARACTER SET utf8 NOT NULL,
  `shop` varchar(80) CHARACTER SET utf8 NOT NULL,
  `matric_school_name` varchar(30) CHARACTER SET utf8 NOT NULL,
  `intermediate_school_name` varchar(30) CHARACTER SET utf8 NOT NULL,
  `graduation_degree` varchar(40) CHARACTER SET utf8 NOT NULL,
  `graduation_college` varchar(40) CHARACTER SET utf8 NOT NULL,
  `p_graduation_degree` varchar(40) CHARACTER SET utf8 NOT NULL,
  `p_graduation_college` varchar(40) CHARACTER SET utf8 NOT NULL,
  `other_degree` varchar(40) CHARACTER SET utf8 NOT NULL,
  `other_college` varchar(40) CHARACTER SET utf8 NOT NULL,
  `date_of_birth` date NOT NULL,
  `fund_account_type` varchar(40) CHARACTER SET utf8 NOT NULL,
  `gender` enum('male','female') CHARACTER SET utf8 NOT NULL,
  `Identification_type` varchar(30) CHARACTER SET utf8 NOT NULL,
  `Identification_number` varchar(30) CHARACTER SET utf8 NOT NULL,
  `pan_number` varchar(10) CHARACTER SET utf8 NOT NULL,
  `flat_no` varchar(12) CHARACTER SET utf8 NOT NULL,
  `estab_address1` varchar(50) CHARACTER SET utf8 NOT NULL,
  `estab_address2` varchar(50) CHARACTER SET utf8 NOT NULL,
  `estab_city` varchar(50) CHARACTER SET utf8 NOT NULL,
  `estab_taluka` varchar(50) CHARACTER SET utf8 NOT NULL,
  `estab_district` varchar(50) CHARACTER SET utf8 NOT NULL,
  `estab_state` varchar(50) CHARACTER SET utf8 NOT NULL,
  `estab_country` varchar(50) CHARACTER SET utf8 NOT NULL,
  `estab_pincode` int(10) NOT NULL,
  `res_type` varchar(15) CHARACTER SET utf8 NOT NULL,
  `res_address1` varchar(50) CHARACTER SET utf8 NOT NULL,
  `res_address2` varchar(50) CHARACTER SET utf8 NOT NULL,
  `res_city` varchar(50) CHARACTER SET utf8 NOT NULL,
  `res_taluka` varchar(50) CHARACTER SET utf8 NOT NULL,
  `res_district` varchar(50) CHARACTER SET utf8 NOT NULL,
  `res_state` varchar(50) CHARACTER SET utf8 NOT NULL,
  `res_country` varchar(50) CHARACTER SET utf8 NOT NULL,
  `res_pincode` int(10) NOT NULL,
  `bank_name` int(50) NOT NULL,
  `bank_account_number` int(35) NOT NULL,
  `team_manager_approval` varchar(12) CHARACTER SET utf8 NOT NULL,
  `bank_id` int(30) NOT NULL,
  `bank_location` varchar(100) CHARACTER SET utf8 NOT NULL,
  `bank_city` varchar(30) CHARACTER SET utf8 NOT NULL,
  `bank_ifsc_code` varchar(30) CHARACTER SET utf8 NOT NULL,
  `branch_id` int(11) NOT NULL,
  `bank_area` varchar(30) CHARACTER SET utf8 NOT NULL,
  `bank_branch_id` int(11) NOT NULL,
  `operation_head_approval` varchar(12) CHARACTER SET utf8 NOT NULL,
  `bank_approval` varchar(12) CHARACTER SET utf8 NOT NULL,
  `amount_bal` int(11) NOT NULL,
  `closure_request` varchar(512) CHARACTER SET utf8 NOT NULL,
  `closure_date` datetime NOT NULL,
  `occupation` varchar(30) CHARACTER SET utf8 NOT NULL,
  `id_proof1` varchar(30) CHARACTER SET utf8 NOT NULL,
  `id_proof2` varchar(30) CHARACTER SET utf8 NOT NULL,
  `address_proof` varchar(30) CHARACTER SET utf8 NOT NULL,
  `annual_income` int(15) NOT NULL,
  `computer_literacy` varchar(30) CHARACTER SET utf8 NOT NULL,
  `political_linkage` varchar(10) CHARACTER SET utf8 NOT NULL,
  `declaration` varchar(10) CHARACTER SET utf8 NOT NULL,
  `place` varchar(30) CHARACTER SET utf8 NOT NULL,
  `fee_code` varchar(20) CHARACTER SET utf8 NOT NULL,
  `date_created` datetime NOT NULL,
  `date_modified` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of t_agent_details
-- ----------------------------

-- ----------------------------
-- Table structure for `t_agent_docs`
-- ----------------------------
DROP TABLE IF EXISTS `t_agent_docs`;
CREATE TABLE `t_agent_docs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `date_created` datetime NOT NULL,
  `status` varchar(12) NOT NULL,
  `agent_id` int(11) NOT NULL,
  `type` varchar(20) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of t_agent_docs
-- ----------------------------

-- ----------------------------
-- Table structure for `t_agents`
-- ----------------------------
DROP TABLE IF EXISTS `t_agents`;
CREATE TABLE `t_agents` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `afn` varchar(30) CHARACTER SET utf8 NOT NULL,
  `username` varchar(50) DEFAULT NULL,
  `email` varchar(50) CHARACTER SET utf8 NOT NULL,
  `password` varchar(40) CHARACTER SET utf8 NOT NULL,
  `status` varchar(12) CHARACTER SET utf8 NOT NULL,
  `activation_code` varchar(50) CHARACTER SET utf8 NOT NULL,
  `agent_code` varchar(50) CHARACTER SET utf8 NOT NULL,
  `principle_distributor_id` int(11) NOT NULL,
  `mobile1` varchar(15) CHARACTER SET utf8 NOT NULL,
  `mobile2` varchar(15) CHARACTER SET utf8 NOT NULL,
  `date_created` datetime NOT NULL,
  `date_modified` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of t_agents
-- ----------------------------
INSERT INTO `t_agents` VALUES ('17', '1234ed', 'vikram', 'vikram@transerv.co.in', 'b07eea53139336b72ce23bef80bef437c8ceb608', 'active', '', '', '0', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

-- ----------------------------
-- Table structure for `t_flags`
-- ----------------------------
DROP TABLE IF EXISTS `t_flags`;
CREATE TABLE `t_flags` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `description` varchar(255) NOT NULL,
  `active_on_dev` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `active_on_prod` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_name1` (`name`) USING BTREE,
  KEY `idx_name1` (`name`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of t_flags
-- ----------------------------
INSERT INTO `t_flags` VALUES ('1', 'operation-flags', 'Allows user to manage the flags', '1', '0');
INSERT INTO `t_flags` VALUES ('2', 'operation-groups', 'Allows user to manage the user groups', '1', '0');
INSERT INTO `t_flags` VALUES ('3', 'operation-index', 'Default entry point in the application', '1', '0');
INSERT INTO `t_flags` VALUES ('4', 'operation-privileges', 'Allows the users to perform CRUD operations on privileges', '1', '0');
INSERT INTO `t_flags` VALUES ('5', 'operation-profile', 'Allows user to manage their profile data', '1', '0');
INSERT INTO `t_flags` VALUES ('6', 'operation-system', 'Allow the admins to manage critical info, users, groups, permissions, etc.', '1', '0');
INSERT INTO `t_flags` VALUES ('7', 'operation-users', 'Allows the users to perform CRUD operations on other users', '1', '0');
INSERT INTO `t_flags` VALUES ('8', 'agent-index', 'Default entry point in the application', '1', '0');
INSERT INTO `t_flags` VALUES ('9', 'operation-testing', 'Some testing permissions', '1', '0');
INSERT INTO `t_flags` VALUES ('10', 'agent-testing', 'Some testing permissions', '1', '0');
INSERT INTO `t_flags` VALUES ('11', 'agent-profile', 'Allow user to perform CRUD operation on privileges', '1', '0');
INSERT INTO `t_flags` VALUES ('12', 'agent-reports', 'Reports for agents', '1', '0');

-- ----------------------------
-- Table structure for `t_flippers`
-- ----------------------------
DROP TABLE IF EXISTS `t_flippers`;
CREATE TABLE `t_flippers` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `group_id` int(11) unsigned NOT NULL,
  `flag_id` int(11) unsigned NOT NULL,
  `privilege_id` int(11) unsigned NOT NULL,
  `allow` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_group_id` (`group_id`),
  KEY `idx_flag_id` (`flag_id`),
  KEY `idx_privilege_id` (`privilege_id`)
) ENGINE=InnoDB AUTO_INCREMENT=51 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of t_flippers
-- ----------------------------
INSERT INTO `t_flippers` VALUES ('17', '2', '8', '26', '1');
INSERT INTO `t_flippers` VALUES ('18', '2', '8', '27', '1');
INSERT INTO `t_flippers` VALUES ('19', '2', '8', '30', '1');
INSERT INTO `t_flippers` VALUES ('20', '2', '11', '31', '1');
INSERT INTO `t_flippers` VALUES ('21', '2', '11', '32', '1');
INSERT INTO `t_flippers` VALUES ('34', '3', '8', '26', '1');
INSERT INTO `t_flippers` VALUES ('35', '3', '8', '27', '1');
INSERT INTO `t_flippers` VALUES ('36', '3', '8', '30', '1');
INSERT INTO `t_flippers` VALUES ('37', '3', '11', '31', '1');
INSERT INTO `t_flippers` VALUES ('38', '3', '11', '32', '1');
INSERT INTO `t_flippers` VALUES ('39', '3', '2', '4', '1');
INSERT INTO `t_flippers` VALUES ('40', '3', '2', '5', '1');
INSERT INTO `t_flippers` VALUES ('41', '3', '2', '6', '1');
INSERT INTO `t_flippers` VALUES ('42', '3', '2', '7', '1');
INSERT INTO `t_flippers` VALUES ('43', '3', '2', '8', '1');
INSERT INTO `t_flippers` VALUES ('44', '3', '5', '14', '1');
INSERT INTO `t_flippers` VALUES ('45', '3', '5', '15', '1');
INSERT INTO `t_flippers` VALUES ('46', '3', '5', '16', '1');
INSERT INTO `t_flippers` VALUES ('47', '3', '5', '17', '1');
INSERT INTO `t_flippers` VALUES ('48', '3', '5', '18', '1');
INSERT INTO `t_flippers` VALUES ('49', '3', '6', '19', '1');
INSERT INTO `t_flippers` VALUES ('50', '3', '6', '20', '1');

-- ----------------------------
-- Table structure for `t_groups`
-- ----------------------------
DROP TABLE IF EXISTS `t_groups`;
CREATE TABLE `t_groups` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(25) DEFAULT NULL,
  `parent_id` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_name` (`name`),
  KEY `idx_parent_id` (`parent_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of t_groups
-- ----------------------------
INSERT INTO `t_groups` VALUES ('1', 'administrators', '0');
INSERT INTO `t_groups` VALUES ('2', 'guests', '0');
INSERT INTO `t_groups` VALUES ('3', 'members', '0');
INSERT INTO `t_groups` VALUES ('4', 'Test User', '3');

-- ----------------------------
-- Table structure for `t_operation_users`
-- ----------------------------
DROP TABLE IF EXISTS `t_operation_users`;
CREATE TABLE `t_operation_users` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `firstname` varchar(20) NOT NULL,
  `lastname` varchar(40) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(40) NOT NULL,
  `password_valid` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `email` varchar(340) NOT NULL,
  `phone_number` varchar(20) DEFAULT NULL,
  `last_login` timestamp NULL DEFAULT NULL,
  `last_password_update` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_username` (`username`),
  KEY `idx_email` (`email`(255))
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of t_operation_users
-- ----------------------------
INSERT INTO `t_operation_users` VALUES ('1', 'Admin', '', 'vikram', 'bf0ecf4915c10e24cc372612a9604937e4ee55ce', '0', 'vikram@transerv.co.in', null, '2012-10-29 13:28:22', null);
INSERT INTO `t_operation_users` VALUES ('2', 'test', 'test', 'test', '633f459e809c068a704c0a1189462b7c1bae0106', '0', 'vikram0207@gmail.com', '9899195914', null, '2012-10-29 15:54:31');

-- ----------------------------
-- Table structure for `t_operation_users_groups`
-- ----------------------------
DROP TABLE IF EXISTS `t_operation_users_groups`;
CREATE TABLE `t_operation_users_groups` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `group_id` int(11) unsigned NOT NULL,
  `user_id` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_user_group` (`group_id`,`user_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_group_id` (`group_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of t_operation_users_groups
-- ----------------------------
INSERT INTO `t_operation_users_groups` VALUES ('1', '1', '1');
INSERT INTO `t_operation_users_groups` VALUES ('2', '3', '2');

-- ----------------------------
-- Table structure for `t_privileges`
-- ----------------------------
DROP TABLE IF EXISTS `t_privileges`;
CREATE TABLE `t_privileges` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `flag_id` varchar(20) NOT NULL,
  `description` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_name_flag_id` (`name`,`flag_id`),
  KEY `idx_resource_id` (`flag_id`)
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of t_privileges
-- ----------------------------
INSERT INTO `t_privileges` VALUES ('1', 'index', '1', 'Allows the user to view all the flags registered in the application');
INSERT INTO `t_privileges` VALUES ('2', 'toggleprod', '1', 'Change the active status of a flag on production');
INSERT INTO `t_privileges` VALUES ('3', 'toggledev', '1', 'Change the active status of a flag on development');
INSERT INTO `t_privileges` VALUES ('4', 'index', '2', 'Allows the user to view all the user groups registered\nin the application');
INSERT INTO `t_privileges` VALUES ('5', 'add', '2', 'Allows the user to add another user group in the\napplication');
INSERT INTO `t_privileges` VALUES ('6', 'edit', '2', 'Edits an existing user group');
INSERT INTO `t_privileges` VALUES ('7', 'delete', '2', 'Allows the user to delete an existing user group. All the users attached to\nthis group *WILL NOT* be deleted, they will just lose all');
INSERT INTO `t_privileges` VALUES ('8', 'flippers', '2', 'Allows the user to manage individual permissions for each\nuser group');
INSERT INTO `t_privileges` VALUES ('9', 'index', '3', 'Controller\'s entry point');
INSERT INTO `t_privileges` VALUES ('10', 'index', '4', 'Allows the user to view all the permissions registered\nin the application');
INSERT INTO `t_privileges` VALUES ('11', 'add', '4', 'Allows the user to add another privilege in the application');
INSERT INTO `t_privileges` VALUES ('12', 'edit', '4', 'Edits an existing privilege');
INSERT INTO `t_privileges` VALUES ('13', 'delete', '4', 'Allows the user to delete an existing privilege. All the flippers related to\nthis privilege will be removed');
INSERT INTO `t_privileges` VALUES ('14', 'index', '5', 'Allows users to see their dashboards');
INSERT INTO `t_privileges` VALUES ('15', 'edit', '5', 'Allows the users to update their profiles');
INSERT INTO `t_privileges` VALUES ('16', 'change-password', '5', 'Allows users to change their passwords');
INSERT INTO `t_privileges` VALUES ('17', 'login', '5', 'Allows users to log into the application');
INSERT INTO `t_privileges` VALUES ('18', 'logout', '5', 'Allows users to log out of the application');
INSERT INTO `t_privileges` VALUES ('19', 'index', '6', 'Controller\'s entry point');
INSERT INTO `t_privileges` VALUES ('20', 'example', '6', 'Theme example page');
INSERT INTO `t_privileges` VALUES ('21', 'index', '7', 'Allows users to see all other users that are registered in\nthe application');
INSERT INTO `t_privileges` VALUES ('22', 'add', '7', 'Allows users to add new users in the application\n(should be reserved for administrators)');
INSERT INTO `t_privileges` VALUES ('23', 'edit', '7', 'Allows users to edit another users\' data\n(should be reserved for administrators)');
INSERT INTO `t_privileges` VALUES ('24', 'view', '7', 'Allows users to see other users\' profiles');
INSERT INTO `t_privileges` VALUES ('25', 'delete', '7', 'Allows users to logically delete other users\n(should be reserved for administrators)');
INSERT INTO `t_privileges` VALUES ('26', 'index', '8', 'Controller\'s entry point');
INSERT INTO `t_privileges` VALUES ('27', 'static', '8', 'Static Pages');
INSERT INTO `t_privileges` VALUES ('28', 'zfdebug', '9', 'Debug toolbar');
INSERT INTO `t_privileges` VALUES ('29', 'zfdebug', '10', 'Debug toolbar');
INSERT INTO `t_privileges` VALUES ('30', 'test', '8', 'test');
INSERT INTO `t_privileges` VALUES ('31', 'index', '11', 'profile landing page');
INSERT INTO `t_privileges` VALUES ('32', 'login', '11', 'Agent Login');
