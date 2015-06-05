/*
SQLyog Enterprise - MySQL GUI v7.02 
MySQL - 5.5.27 : Database - zf-rnd
*********************************************************************
*/

/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

CREATE DATABASE /*!32312 IF NOT EXISTS*/`zf-rnd` /*!40100 DEFAULT CHARACTER SET utf8 */;

USE `zf-rnd`;

/*Table structure for table `t_agent_areas` */

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

/*Data for the table `t_agent_areas` */

/*Table structure for table `t_agent_details` */

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

/*Data for the table `t_agent_details` */

/*Table structure for table `t_agent_docs` */

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

/*Data for the table `t_agent_docs` */

/*Table structure for table `t_agents` */

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

/*Data for the table `t_agents` */

insert  into `t_agents`(`id`,`afn`,`username`,`email`,`password`,`status`,`activation_code`,`agent_code`,`principle_distributor_id`,`mobile1`,`mobile2`,`date_created`,`date_modified`) values (17,'1234ed','vikram','vikram@transerv.co.in','b07eea53139336b72ce23bef80bef437c8ceb608','active','','',0,'','','0000-00-00 00:00:00','0000-00-00 00:00:00');

/*Table structure for table `t_cardholder_details` */

DROP TABLE IF EXISTS `t_cardholder_details`;

CREATE TABLE `t_cardholder_details` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cardholder_id` int(11) NOT NULL,
  `first_name` varchar(20) CHARACTER SET utf8 NOT NULL,
  `middle_name` varchar(20) CHARACTER SET utf8 NOT NULL,
  `last_name` varchar(20) CHARACTER SET utf8 NOT NULL,
  `mobile_number` varchar(15) NOT NULL,
  `alternate_contact_numberv` varchar(15) NOT NULL,
  `father_first_name` varchar(20) CHARACTER SET utf8 NOT NULL,
  `father_middle_name` varchar(20) CHARACTER SET utf8 NOT NULL,
  `father_last_name` varchar(20) CHARACTER SET utf8 NOT NULL,
  `mother_maiden_name` varchar(20) CHARACTER SET utf8 NOT NULL,
  `spouse_first_name` varchar(20) CHARACTER SET utf8 NOT NULL,
  `spouse_middle_name` varchar(20) CHARACTER SET utf8 NOT NULL,
  `spouse_last_name` varchar(20) CHARACTER SET utf8 NOT NULL,
  `res_type` varchar(15) CHARACTER SET utf8 NOT NULL,
  `nationality` varchar(30) CHARACTER SET utf8 NOT NULL,
  `date_of_birth` date NOT NULL,
  `gender` enum('M','F') CHARACTER SET utf8 NOT NULL,
  `flat_number` varchar(12) CHARACTER SET utf8 NOT NULL,
  `address_line1` varchar(100) CHARACTER SET utf8 NOT NULL,
  `address_line2` varchar(100) CHARACTER SET utf8 NOT NULL,
  `city` varchar(50) CHARACTER SET utf8 NOT NULL,
  `taluka` varchar(50) CHARACTER SET utf8 NOT NULL,
  `district` varchar(50) CHARACTER SET utf8 NOT NULL,
  `state` varchar(50) CHARACTER SET utf8 NOT NULL,
  `country` varchar(50) CHARACTER SET utf8 NOT NULL,
  `pincode` int(10) NOT NULL,
  `landmark` varchar(150) CHARACTER SET utf8 NOT NULL,
  `customer_mvc_type` enum('MVCC','MVCI') CHARACTER SET utf8 NOT NULL,
  `application_status` enum('Incomplete','Complete') CHARACTER SET utf8 NOT NULL,
  `status` enum('Active','Pending','Blocked') CHARACTER SET utf8 DEFAULT NULL,
  `block_reason` varchar(50) CHARACTER SET utf8 DEFAULT NULL,
  `agent_id` int(11) DEFAULT NULL,
  `caste_category` varchar(10) CHARACTER SET utf8 DEFAULT NULL,
  `profession` varchar(25) CHARACTER SET utf8 DEFAULT NULL,
  `annual_income` int(15) DEFAULT NULL,
  `pan_number` varchar(15) CHARACTER SET utf8 DEFAULT NULL,
  `nominee_first_name` varchar(20) CHARACTER SET utf8 DEFAULT NULL,
  `nominee_middle_name` varchar(20) CHARACTER SET utf8 DEFAULT NULL,
  `nominee_last_name` varchar(20) CHARACTER SET utf8 DEFAULT NULL,
  `date_of_birth_nominee` date DEFAULT NULL,
  `relationship_with_applicant` varchar(20) CHARACTER SET utf8 DEFAULT NULL,
  `declaration` varchar(10) CHARACTER SET utf8 DEFAULT NULL,
  `place` varchar(30) CHARACTER SET utf8 DEFAULT NULL,
  `introducer_first_name` varchar(20) CHARACTER SET utf8 DEFAULT NULL,
  `introducer_middle_name` varchar(20) CHARACTER SET utf8 DEFAULT NULL,
  `introducer_last_name` varchar(20) CHARACTER SET utf8 DEFAULT NULL,
  `introducer_account_number` varchar(30) CHARACTER SET utf8 DEFAULT NULL,
  `introducer_branch` varchar(30) CHARACTER SET utf8 DEFAULT NULL,
  `introducer_know_since` date DEFAULT NULL,
  `id_proof_attached` enum('Yes','No') CHARACTER SET utf8 DEFAULT NULL,
  `address_proof_attached` enum('Yes','No') CHARACTER SET utf8 DEFAULT NULL,
  `uid_number` varchar(30) CHARACTER SET utf8 DEFAULT NULL,
  `already_bank_account` enum('Yes','No') CHARACTER SET utf8 DEFAULT NULL,
  `vehicle_type` varchar(20) CHARACTER SET utf8 DEFAULT NULL,
  `educational_qualifications` varchar(100) CHARACTER SET utf8 DEFAULT NULL,
  `family_members` int(4) DEFAULT NULL,
  `date_created` datetime DEFAULT NULL,
  `date_modified` datetime DEFAULT NULL,
  `date_activated` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

/*Data for the table `t_cardholder_details` */

/*Table structure for table `t_cardholders` */

DROP TABLE IF EXISTS `t_cardholders`;

CREATE TABLE `t_cardholders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `arn` varchar(10) CHARACTER SET utf8 NOT NULL,
  `product_id` int(11) NOT NULL,
  `activation_code` varchar(50) CHARACTER SET utf8 NOT NULL,
  `status` varchar(12) CHARACTER SET utf8 NOT NULL,
  `date_created` datetime NOT NULL,
  `date_modified` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

/*Data for the table `t_cardholders` */

/*Table structure for table `t_flags` */

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

/*Data for the table `t_flags` */

insert  into `t_flags`(`id`,`name`,`description`,`active_on_dev`,`active_on_prod`) values (1,'operation-flags','Allows user to manage the flags',1,0),(2,'operation-groups','Allows user to manage the user groups',1,0),(3,'operation-index','Default entry point in the application',1,0),(4,'operation-privileges','Allows the users to perform CRUD operations on privileges',1,0),(5,'operation-profile','Allows user to manage their profile data',1,0),(6,'operation-system','Allow the admins to manage critical info, users, groups, permissions, etc.',1,0),(7,'operation-users','Allows the users to perform CRUD operations on other users',1,0),(8,'agent-index','Default entry point in the application',1,0),(9,'operation-testing','Some testing permissions',1,0),(10,'agent-testing','Some testing permissions',1,0),(11,'agent-profile','Allow user to perform CRUD operation on privileges',1,0),(12,'agent-reports','Reports for agents',1,0);

/*Table structure for table `t_flippers` */

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

/*Data for the table `t_flippers` */

insert  into `t_flippers`(`id`,`group_id`,`flag_id`,`privilege_id`,`allow`) values (17,2,8,26,1),(18,2,8,27,1),(19,2,8,30,1),(20,2,11,31,1),(21,2,11,32,1),(34,3,8,26,1),(35,3,8,27,1),(36,3,8,30,1),(37,3,11,31,1),(38,3,11,32,1),(39,3,2,4,1),(40,3,2,5,1),(41,3,2,6,1),(42,3,2,7,1),(43,3,2,8,1),(44,3,5,14,1),(45,3,5,15,1),(46,3,5,16,1),(47,3,5,17,1),(48,3,5,18,1),(49,3,6,19,1),(50,3,6,20,1);

/*Table structure for table `t_groups` */

DROP TABLE IF EXISTS `t_groups`;

CREATE TABLE `t_groups` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(25) DEFAULT NULL,
  `parent_id` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_name` (`name`),
  KEY `idx_parent_id` (`parent_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

/*Data for the table `t_groups` */

insert  into `t_groups`(`id`,`name`,`parent_id`) values (1,'administrators',0),(2,'guests',0),(3,'members',0),(4,'Test User',3);

/*Table structure for table `t_operation_users` */

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

/*Data for the table `t_operation_users` */

insert  into `t_operation_users`(`id`,`firstname`,`lastname`,`username`,`password`,`password_valid`,`email`,`phone_number`,`last_login`,`last_password_update`) values (1,'Admin','','vikram','bf0ecf4915c10e24cc372612a9604937e4ee55ce',0,'vikram@transerv.co.in',NULL,'2012-10-29 13:28:22',NULL),(2,'test','test','test','633f459e809c068a704c0a1189462b7c1bae0106',0,'vikram0207@gmail.com','9899195914',NULL,'2012-10-29 15:54:31');

/*Table structure for table `t_operation_users_groups` */

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

/*Data for the table `t_operation_users_groups` */

insert  into `t_operation_users_groups`(`id`,`group_id`,`user_id`) values (1,1,1),(2,3,2);

/*Table structure for table `t_privileges` */

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

/*Data for the table `t_privileges` */

insert  into `t_privileges`(`id`,`name`,`flag_id`,`description`) values (1,'index','1','Allows the user to view all the flags registered in the application'),(2,'toggleprod','1','Change the active status of a flag on production'),(3,'toggledev','1','Change the active status of a flag on development'),(4,'index','2','Allows the user to view all the user groups registered\nin the application'),(5,'add','2','Allows the user to add another user group in the\napplication'),(6,'edit','2','Edits an existing user group'),(7,'delete','2','Allows the user to delete an existing user group. All the users attached to\nthis group *WILL NOT* be deleted, they will just lose all'),(8,'flippers','2','Allows the user to manage individual permissions for each\nuser group'),(9,'index','3','Controller\'s entry point'),(10,'index','4','Allows the user to view all the permissions registered\nin the application'),(11,'add','4','Allows the user to add another privilege in the application'),(12,'edit','4','Edits an existing privilege'),(13,'delete','4','Allows the user to delete an existing privilege. All the flippers related to\nthis privilege will be removed'),(14,'index','5','Allows users to see their dashboards'),(15,'edit','5','Allows the users to update their profiles'),(16,'change-password','5','Allows users to change their passwords'),(17,'login','5','Allows users to log into the application'),(18,'logout','5','Allows users to log out of the application'),(19,'index','6','Controller\'s entry point'),(20,'example','6','Theme example page'),(21,'index','7','Allows users to see all other users that are registered in\nthe application'),(22,'add','7','Allows users to add new users in the application\n(should be reserved for administrators)'),(23,'edit','7','Allows users to edit another users\' data\n(should be reserved for administrators)'),(24,'view','7','Allows users to see other users\' profiles'),(25,'delete','7','Allows users to logically delete other users\n(should be reserved for administrators)'),(26,'index','8','Controller\'s entry point'),(27,'static','8','Static Pages'),(28,'zfdebug','9','Debug toolbar'),(29,'zfdebug','10','Debug toolbar'),(30,'test','8','test'),(31,'index','11','profile landing page'),(32,'login','11','Agent Login');

/*Table structure for table `t_processor_crns` */

DROP TABLE IF EXISTS `t_processor_crns`;

CREATE TABLE `t_processor_crns` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `crn` varchar(50) DEFAULT NULL,
  `status` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;

/*Data for the table `t_processor_crns` */

insert  into `t_processor_crns`(`id`,`crn`,`status`) values (1,'c00001','active'),(2,'c00002','active'),(3,'c00003','active');

/*Table structure for table `t_product_master` */

DROP TABLE IF EXISTS `t_product_master`;

CREATE TABLE `t_product_master` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(60) CHARACTER SET utf8 NOT NULL,
  `bank_id` int(11) NOT NULL,
  `description` varchar(100) CHARACTER SET utf8 NOT NULL,
  `status` varchar(12) CHARACTER SET utf8 NOT NULL,
  `date_created` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

/*Data for the table `t_product_master` */

insert  into `t_product_master`(`id`,`name`,`bank_id`,`description`,`status`,`date_created`) values (1,'produdct1',1,'very good product','active','2012-10-29 04:33:23');

/*Table structure for table `t_products` */

DROP TABLE IF EXISTS `t_products`;

CREATE TABLE `t_products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_master_id` int(11) NOT NULL,
  `name` varchar(60) CHARACTER SET utf8 NOT NULL,
  `description` varchar(100) CHARACTER SET utf8 NOT NULL,
  `status` varchar(12) CHARACTER SET utf8 NOT NULL,
  `date_created` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;

/*Data for the table `t_products` */

insert  into `t_products`(`id`,`product_master_id`,`name`,`description`,`status`,`date_created`) values (1,1,'new year card','very attractive feature product','active','2012-10-29 04:32:40'),(2,1,'happy shopping','good for shopping','active','2012-10-29 04:35:23');

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
