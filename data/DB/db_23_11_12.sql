-- phpMyAdmin SQL Dump
-- version 3.4.11.1deb1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Nov 23, 2012 at 01:10 PM
-- Server version: 5.5.28
-- PHP Version: 5.4.6-1ubuntu1

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `shmart`
--

-- --------------------------------------------------------

--
-- Table structure for table `t_activation`
--

CREATE TABLE IF NOT EXISTS `t_activation` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `user_type` enum('agent','ops','cardholder','bank','others') NOT NULL,
  `activation_code` varchar(50) NOT NULL,
  `email` varchar(150) NOT NULL,
  `create_datetime` datetime NOT NULL,
  `update_datetime` datetime NOT NULL,
  `activation_status` enum('success','pending') NOT NULL,
  `status` enum('active','inactive') NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `activation_code` (`activation_code`) USING BTREE
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=29 ;

--
-- Dumping data for table `t_activation`
--

INSERT INTO `t_activation` (`id`, `user_id`, `user_type`, `activation_code`, `email`, `create_datetime`, `update_datetime`, `activation_status`, `status`) VALUES
(1, 61, 'agent', '2627e8f33d99728a1a934fee33a05d93f319372d', 'komal52@transerv.co.in', '2012-11-14 18:56:12', '0000-00-00 00:00:00', 'pending', 'inactive'),
(2, 63, 'agent', '7147a1b8bc3daa44bc854680f14470e05efebd11', 'karora@yahoo.com', '2012-11-14 19:14:28', '0000-00-00 00:00:00', 'pending', 'inactive'),
(3, 64, 'agent', '3577ca84a8efef4617ce5c874b60205af7d3455a', 'ch@yahoo.com', '2012-11-14 19:29:59', '0000-00-00 00:00:00', 'pending', 'inactive'),
(4, 65, 'agent', '086d98e02305bdad4751cac49924db4a167f5bf4', 'chgp@yahoo.com', '2012-11-14 19:31:09', '0000-00-00 00:00:00', 'pending', 'inactive'),
(5, 66, 'agent', '573029670e422a563a504fa5215658039f286b0d', 'chgpar@yahoo.com', '2012-11-14 19:32:01', '0000-00-00 00:00:00', 'pending', 'inactive'),
(6, 67, 'agent', 'bf5ef695553f45742e5e4ef039f2ac31e396f34d', 'rajat@yahoo.com', '2012-11-14 19:40:24', '2012-11-15 10:37:57', 'success', 'active'),
(7, 68, 'agent', 'de6b28e966258668118fe48c689b406ef26bd266', 'kp@yahoo.com', '2012-11-15 11:15:23', '2012-11-15 11:21:59', 'success', 'active'),
(8, 69, 'agent', '9631b010089f6a801a11d3a8195ebecc78488638', 'jp@yahoo.com', '2012-11-16 18:44:20', '0000-00-00 00:00:00', 'pending', 'inactive'),
(9, 70, 'agent', '52c78546ee9e1a4da8eab55abb5cbfb85b4ca936', 'kap@yahoo.com', '2012-11-19 12:38:53', '0000-00-00 00:00:00', 'pending', 'inactive'),
(10, 71, 'agent', 'd3806bfb60febfc36d599bd90e579eb3216f3f02', 'vikram0207@gmail.com', '2012-11-19 12:51:08', '2012-11-19 14:16:24', 'success', 'active'),
(11, 72, 'agent', '32e9332d13e4eb1ee8832c9aef98df882a93993f', 'komal.arora82456@gmail.com', '2012-11-19 13:59:18', '0000-00-00 00:00:00', 'pending', 'inactive'),
(12, 73, 'agent', 'dc0d5664a0ec6a9d434db53b9bc753cdc1b426d8', 'komal.a.arora82@gmail.com', '2012-11-19 14:35:13', '0000-00-00 00:00:00', 'pending', 'inactive'),
(13, 74, 'agent', '7457250530d671a4da050593855a5e8c03676258', 'guryashpuri@gmail.com', '2012-11-19 14:48:03', '0000-00-00 00:00:00', 'pending', 'inactive'),
(14, 75, 'agent', '38ab94dd7ec32f282adfe11183dfdc46287d411d', 'komaljalaj.arora82@gmail.com', '2012-11-19 14:53:29', '0000-00-00 00:00:00', 'pending', 'inactive'),
(15, 76, 'agent', '0b3a09f10d9d4825740e3d7b2cfffaf800e5c520', 'komal.arora82@gmail.com', '2012-11-19 15:01:16', '0000-00-00 00:00:00', 'pending', 'inactive'),
(16, 77, 'agent', '50f9d20391fdac0e82ac1c5d4554c56bc7d141e9', 'mini@transerv.co.in', '2012-11-19 15:01:37', '0000-00-00 00:00:00', 'pending', 'inactive'),
(17, 78, 'agent', 'e3ff7c50428c3dc1baca151f8b59af0519385610', 'komal.arora82@gmail.com', '2012-11-19 15:08:15', '0000-00-00 00:00:00', 'pending', 'inactive'),
(18, 79, 'agent', 'ebf46482de66f258b241d612111ea7092c7a9433', 'komal.arora12@gmail.com', '2012-11-19 15:18:26', '0000-00-00 00:00:00', 'pending', 'inactive'),
(19, 80, 'agent', 'b2e2fd725264788e7de7a4a7d84b6473d7a3aa91', 'vikram@transerv.co.in', '2012-11-19 15:26:20', '2012-11-19 15:37:03', 'success', 'active'),
(20, 81, 'agent', '02c8b3023c9334ef5c24f8affcdc5860fd60460b', 'komal.arora82@gmail.com', '2012-11-19 15:47:38', '2012-11-19 15:55:40', 'success', 'active'),
(21, 82, 'agent', '0d4fab152e84cadce170c8c4a12b768f0707c69d', 'mini@transerv.co.in', '2012-11-19 15:57:35', '0000-00-00 00:00:00', 'pending', 'inactive'),
(22, 83, 'agent', 'efde90717c39911e070fefa1f725d86f24d0070c', 'aditya@transev.co.in', '2012-11-19 18:03:55', '0000-00-00 00:00:00', 'pending', 'inactive'),
(23, 81, 'agent', '8dbe1ffe34ce8190c4a40e9eefa0e33835168200', 'komal@transerv.co.in', '2012-11-20 14:34:35', '0000-00-00 00:00:00', 'pending', 'inactive'),
(24, 86, 'agent', '25f0f899407b1fe9ff3989c955d2b746eb379637', 'komal.arora82@gmail.com', '2012-11-20 15:07:45', '0000-00-00 00:00:00', 'pending', 'inactive'),
(25, 86, 'agent', 'e8fa16055d7c2ed66f540ce3a61c8070adb31a2d', 'komal.arora82@gmail.com', '2012-11-20 15:08:40', '2012-11-20 15:12:56', 'success', 'active'),
(26, 86, 'agent', '044233c669682c617cecbe3d8855683b2ff5d326', 'komal.arora82@gmail.com', '2012-11-20 15:09:50', '0000-00-00 00:00:00', 'pending', 'inactive'),
(27, 91, 'agent', '9ac15844168e0fcc173a491184fb6f717f92e569', 'abc@yahoo.com', '2012-11-22 16:50:19', '0000-00-00 00:00:00', 'pending', 'inactive'),
(28, 75, 'agent', 'ed9d03eaf4b10801c6f5364b2bf4582b5d8d315a', 'komaljalaj.arora82@gmail.com', '2012-11-22 16:57:25', '0000-00-00 00:00:00', 'pending', 'inactive');

-- --------------------------------------------------------

--
-- Table structure for table `t_agents`
--

CREATE TABLE IF NOT EXISTS `t_agents` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `agent_code` bigint(20) unsigned NOT NULL,
  `email` varchar(50) CHARACTER SET utf8 NOT NULL,
  `password` varchar(40) CHARACTER SET utf8 NOT NULL,
  `title` enum('mr','mrs','ms','dr','prof') NOT NULL,
  `first_name` varchar(35) NOT NULL,
  `middle_name` varchar(35) NOT NULL,
  `last_name` varchar(35) NOT NULL,
  `ip` bigint(20) unsigned NOT NULL,
  `mobile1` varchar(15) CHARACTER SET utf8 NOT NULL,
  `mobile2` varchar(15) CHARACTER SET utf8 NOT NULL,
  `auth_code` varchar(20) NOT NULL,
  `num_login_attempts` tinyint(4) NOT NULL,
  `activation_id` int(11) NOT NULL,
  `activation_status` enum('success','pending') NOT NULL,
  `enroll_status` enum('approved','pending') NOT NULL,
  `status` enum('blocked','unblocked','locked') NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=93 ;

--
-- Dumping data for table `t_agents`
--

INSERT INTO `t_agents` (`id`, `agent_code`, `email`, `password`, `title`, `first_name`, `middle_name`, `last_name`, `ip`, `mobile1`, `mobile2`, `auth_code`, `num_login_attempts`, `activation_id`, `activation_status`, `enroll_status`, `status`) VALUES
(41, 23456, 'komal12345@transerv.co.in', 'b07eea53139336b72ce23bef80bef437c8ceb608', 'mr', 'Komal', '', 'Puri', 0, '9810712345', '123456', '', 0, 0, '', 'approved', 'unblocked'),
(42, 3456732312, 'mini@transerv.co.in', 'b07eea53139336b72ce23bef80bef437c8ceb608', 'mr', 'Vikarm', 'A', 'Singh', 0, '9810780622', '12', '830349', 0, 0, 'success', 'approved', 'unblocked'),
(43, 12345678, 'ashish@transerv.co.in', 'b07eea53139336b72ce23bef80bef437c8ceb608', 'mr', 'Ashish', '', 'Vats', 0, '9712298518', '', '835060', 0, 0, 'success', 'approved', 'unblocked'),
(44, 34345678912, 'vikram@transerv.co.in', 'b07eea53139336b72ce23bef80bef437c8ceb608', 'mr', 'Vikram', '', 'Singh', 0, '9810780234', '', '612670', 0, 0, 'success', 'approved', 'unblocked'),
(45, 12345644, 'ashish9@transerv.co.in', 'b07eea53139336b72ce23bef80bef437c8ceb608', 'mr', 'Ashish', '', 'Vats', 0, '9810780890', '', '', 0, 0, 'success', 'approved', 'unblocked'),
(49, 0, 'example@gmail.com', '1122334455', 'mr', '', '', '', 0, '3344556677', '', '', 0, 0, '', 'approved', 'unblocked'),
(50, 0, 'komal.arora821@gmail.com', '3d3bb5d38b', 'mr', '', '', '', 0, '8877996655', '', '', 0, 0, '', 'approved', 'unblocked'),
(51, 0, 'komal122@transerv.co.in', 'eb616f2e28', 'mr', '', '', '', 0, '9810780658', '', '', 0, 0, '', 'approved', 'unblocked'),
(53, 12345, 'komalp@transerv.co.in', 'b07eea53139336b72ce23bef80bef437c8ceb608', 'mr', 'komal', 'Arora', 'Puri', 0, '9810780678', '', '892659', 0, 0, 'success', 'approved', 'unblocked'),
(54, 0, 'komal4545@transerv.co.in', 'db2693274564b6ed4023cbbdfb8d8c89f0439890', 'mr', '', '', '', 0, '9810780111', '', '', 0, 0, '', 'approved', 'unblocked'),
(58, 0, 'guryash@gmail.com', '8fa562af6c1ebb29532e8221c3e936cc279b064a', 'mr', '', '', '', 0, '9810780222', '', '', 0, 0, '', 'approved', 'unblocked'),
(59, 0, 'guryash.puri@gmail.com', 'a5236ca21d913b7f474c1e8fb460049158a0b83b', 'mr', '', '', '', 0, '2233445566', '', '', 0, 0, '', 'approved', 'unblocked'),
(60, 0, 'komal5@transerv.co.in', '911dbbf0de0dcc8afc5ffe772124e01f581855e4', 'mrs', 'Komal', '', 'Puri', 1270, '1133557799', '', '', 0, 0, 'pending', 'pending', 'unblocked'),
(61, 26476050061, 'mini11@transerv.co.in', '27ed36ddb470e32836b6ff8192593bb21da015a2', 'mrs', 'Komal', '', 'Puri', 1270, '1133557788', '', '', 0, 0, 'success', 'approved', 'unblocked'),
(62, 31089610062, 'komalarora@transerv.co.in', '56a4cce48f12339a58dc40add4cc434c2250cd36', 'mr', 'Komal', '', 'Puri', 1270, '1223344556', '', '', 0, 0, 'pending', 'pending', 'unblocked'),
(63, 13456560063, 'karora@yahoo.com', '7c7b751b43497a2716661709d26cc1e8ffa3aa39', 'ms', 'Komal', '', 'Puri', 1270, '9988776655', '', '', 0, 1, 'pending', 'pending', 'unblocked'),
(64, 24404470064, 'ch@yahoo.com', '4e1bbbceca8602e9efc4c503c70d0a81b2121cbf', 'ms', 'Komal', '', 'Puri', 1270, '9999642182', '', '', 0, 1, 'pending', 'pending', 'unblocked'),
(65, 22884690065, 'chgp@yahoo.com', 'ba2a16087393e77780395a0c245d49cd57f07bc7', 'ms', 'Komal', '', 'Puri', 1270, '9999642181', '', '', 0, 1, 'pending', 'pending', 'unblocked'),
(66, 17553250066, 'chgpar@yahoo.com', '97fd4f0cc30830424ccc7b12922b271b9ecfbca2', 'ms', 'Komal', '', 'Puri', 1270, '9999642882', '', '', 0, 1, 'pending', 'pending', 'unblocked'),
(67, 42616710067, 'rajat@yahoo.com', '7f09d675c9fd672c67ac4cf9dbd3ba25d431b059', 'mrs', 'Komal', '', 'Puri', 1270, '9868111111', '', '', 0, 6, 'success', 'pending', 'unblocked'),
(68, 22029980068, 'kp@yahoo.com', 'e73ce45dd86a10e34eb5e334ecefe7e157fb6ca8', 'mrs', 'Komal', '', 'Puri', 1270, '1122334455', '', '', 0, 7, 'success', 'pending', 'unblocked'),
(69, 41801170069, 'jp@yahoo.com', '325ca66d832fb1840a4c44784434dc78e18a304f', 'mrs', 'Komal', '', 'Puri', 1270, '9810780699', '', '', 0, 8, 'pending', 'pending', 'unblocked'),
(70, 41085240070, 'kap@yahoo.com', 'a2a2504f6ac6b1ab9289129a9204161f0d57a130', 'mrs', 'Komal', '', 'Puri', 1270, '9810780689', '', '', 0, 9, 'pending', 'pending', 'unblocked'),
(71, 32764050071, 'vikram0207@gmail.com', '83b233130b695bf459046b8a7659949006edadaa', 'mr', 'Vikram', '', 'Singh', 122160, '9711198999', '', '', 1, 10, 'success', 'approved', 'unblocked'),
(72, 39795380072, 'komal.arora82456@gmail.com', '36474726e230ca6ee9b61cb88d5014367f6f9c9b', 'mrs', 'Komal', '', 'Puri', 1270, '8787878787', '', '', 0, 11, 'pending', 'pending', 'unblocked'),
(73, 34659790073, 'komal.a.arora82@gmail.com', '5a18a7db28d52c7941323f6027ae5ea0eb599d60', 'mrs', 'Komal', '', 'Puri', 1270, '9999999999', '', '', 0, 12, 'pending', 'pending', 'unblocked'),
(74, 36763910074, 'guryashpuri@gmail.com', '9ecb7143fe9fbb52a9586d2c9fcdf0b19a4307fe', 'mrs', 'Komal', '', 'Puri', 1270, '2244556677', '', '', 0, 13, 'pending', 'pending', 'unblocked'),
(75, 16012240075, 'komaljalaj.arora82@gmail.com', 'f33d30614c2e3410b2dc359bb6441b4c2bdbeeb5', 'mrs', 'Komal', '', 'Puri', 1270, '1111111111', '', '', 0, 28, 'pending', 'approved', 'unblocked'),
(76, 31529200076, 'komal.puri.arora82@gmail.com', 'f6ed09515409d5a053da79d119ad0a7f225403c4', 'mrs', 'Komal', 'Arora', 'Puri', 1270, '9810780677', '', '', 0, 15, 'pending', 'pending', 'unblocked'),
(78, 31529200078, 'komal..parora82@gmail.com', '214165a9a878fabc366688517c6acdb14c484851', 'mrs', 'Komal', '', 'Puri', 1270, '9868686868', '', '', 0, 17, 'pending', 'pending', 'unblocked'),
(79, 64202480079, 'komal.arora12@gmail.com', '9682f025b863c23961e6da96d306d365ef263baa', 'mrs', 'Komal', '', 'Puri', 1270, '9868686868', '', '', 0, 18, 'pending', 'pending', 'unblocked'),
(80, 36640600080, 'vikram@transerv.co.in', '723f0bed8edce85ba9e9118302d752c184dd8767', 'mr', 'Vikram', '', 'Singh', 122160, '9810780677', '', '', 0, 19, 'success', 'approved', 'unblocked'),
(81, 31529200081, 'komalarpuri@transerv.co.in', '2e239321d3ea22b56f3b1c8a854825c10a9df793', 'mrs', 'Komal', '', 'Puri', 1270, '9810780777', '', '', 0, 23, 'pending', 'approved', 'unblocked'),
(82, 36832450082, 'mini12@transerv.co.in', 'fa37fbf5403fc43b6df2444e9978cf8a2a64a1af', 'mrs', 'Mini', '', 'Biswal', 122160, '9111198518', '', '', 0, 21, 'pending', 'pending', 'unblocked'),
(83, 39094560083, 'aditya@transerv.co.in', '004f7a32ffb067536bc731f4c379df637614548e', 'mr', 'Aditya', '', 'Gupta', 122160, '9920799880', '', '', 0, 22, 'success', 'approved', 'unblocked'),
(86, 31529200086, 'komal.arora82@gmail.com', 'e4eb90471e8bdddfd9b066f1d26a49bb394adf8e', 'mrs', 'Komal', '', 'Puri', 1270, '1234567898', '', '', 0, 26, 'success', 'approved', 'unblocked'),
(87, 0, 'ambuj@yahoo.com', '', 'mrs', 'Komal', '', 'Puri', 1270, '8787878799', '', '', 0, 0, 'pending', 'pending', 'unblocked'),
(88, 22432910088, 'komal1452@transerv.co.in', '', 'mrs', 'Komal', '', 'Puri', 1270, '9810780676', '', '', 0, 0, 'pending', 'pending', 'unblocked'),
(89, 39481650089, 'ashishdfd@transerv.co.in', '', 'ms', 'Neeta', '', 'Rathore', 122160, '9810780667', '', '', 0, 0, 'pending', 'pending', 'unblocked'),
(90, 18920720090, 'k@gmail.com', '', 'mrs', 'Komal', '', 'Puri', 1270, '8888855555', '', '', 0, 0, 'pending', 'pending', 'unblocked'),
(91, 23859070091, 'abc@yahoo.com', '81ce39cbdb7d180f8e9d72410ffa2b9f5201b7d9', 'mrs', 'Komal', '', 'Puri', 1270, '7878787878', '', '', 0, 27, 'pending', 'approved', 'blocked'),
(92, 27790990092, 'a@yahoo.com', '', 'ms', 'Komal', '', 'Puri', 1270, '4444444444', '', '', 0, 0, 'pending', 'pending', 'unblocked');

-- --------------------------------------------------------

--
-- Table structure for table `t_agent_areas`
--

CREATE TABLE IF NOT EXISTS `t_agent_areas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `agent_id` int(11) NOT NULL,
  `village` varchar(50) CHARACTER SET utf8 NOT NULL,
  `taluka` varchar(50) CHARACTER SET utf8 NOT NULL,
  `state` int(10) NOT NULL,
  `pincode` int(10) NOT NULL,
  `date_created` datetime NOT NULL,
  `date_updated` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `t_agent_balance`
--

CREATE TABLE IF NOT EXISTS `t_agent_balance` (
  `agent_id` int(11) NOT NULL,
  `amount` decimal(9,4) NOT NULL DEFAULT '0.0000',
  `block_amount` decimal(9,4) NOT NULL DEFAULT '0.0000',
  `date_modified` datetime DEFAULT NULL,
  PRIMARY KEY (`agent_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `t_agent_balance`
--

INSERT INTO `t_agent_balance` (`agent_id`, `amount`, `block_amount`, `date_modified`) VALUES
(41, 1000.0000, 0.0000, '2012-11-08 00:00:00'),
(42, 1500.0000, 0.0000, '2012-11-21 13:01:02'),
(43, 4200.0000, 0.0000, '2012-11-23 15:31:18'),
(44, 1000.0000, 0.0000, '2012-11-08 00:00:00'),
(45, 1500.0000, 0.0000, '2012-11-08 00:00:00'),
(50, 1230.0000, 0.0000, '2012-11-08 14:04:32'),
(53, 1334.0000, 0.0000, '2012-11-08 14:06:10'),
(61, 7000.0000, 0.0000, '2012-11-19 14:26:47'),
(71, 6000.0000, 0.0000, '2012-11-19 18:39:06'),
(80, 2000.0000, 0.0000, '2012-11-19 15:35:51'),
(81, 5000.0000, 0.0000, '2012-11-19 16:27:31'),
(83, 9750.0000, 0.0000, '2012-11-19 19:14:35');

-- --------------------------------------------------------

--
-- Table structure for table `t_agent_details`
--

CREATE TABLE IF NOT EXISTS `t_agent_details` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `agent_id` int(11) NOT NULL,
  `afn` varchar(30) NOT NULL,
  `title` enum('Mr','Mrs','Ms','Dr','Prof') NOT NULL,
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
  `education_level` varchar(20) NOT NULL,
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
  `registration_type` enum('self','ops','agent') NOT NULL,
  `registered_id` int(11) NOT NULL,
  `status` enum('active','inactive') NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=44 ;

--
-- Dumping data for table `t_agent_details`
--

INSERT INTO `t_agent_details` (`id`, `agent_id`, `afn`, `title`, `father_first_name`, `father_middle_name`, `father_last_name`, `spouse_first_name`, `spouse_middle_name`, `spouse_last_name`, `mother_maiden_name`, `eatab_name`, `home`, `office`, `shop`, `education_level`, `matric_school_name`, `intermediate_school_name`, `graduation_degree`, `graduation_college`, `p_graduation_degree`, `p_graduation_college`, `other_degree`, `other_college`, `date_of_birth`, `fund_account_type`, `gender`, `Identification_type`, `Identification_number`, `pan_number`, `flat_no`, `estab_address1`, `estab_address2`, `estab_city`, `estab_taluka`, `estab_district`, `estab_state`, `estab_country`, `estab_pincode`, `res_type`, `res_address1`, `res_address2`, `res_city`, `res_taluka`, `res_district`, `res_state`, `res_country`, `res_pincode`, `bank_name`, `bank_account_number`, `team_manager_approval`, `bank_id`, `bank_location`, `bank_city`, `bank_ifsc_code`, `branch_id`, `bank_area`, `bank_branch_id`, `operation_head_approval`, `bank_approval`, `amount_bal`, `closure_request`, `closure_date`, `occupation`, `id_proof1`, `id_proof2`, `address_proof`, `annual_income`, `computer_literacy`, `political_linkage`, `declaration`, `place`, `fee_code`, `date_created`, `date_modified`, `registration_type`, `registered_id`, `status`) VALUES
(1, 41, '', '', '', '', '', '', '', '', '', '', 'Pitam Pura', 'Gurgaon', 'NYC,USA', '', 'Ramjas School', 'Ramjas School', 'BIS', 'DU', 'MCA', 'PTU', '', '', '1982-12-29', 'By Agent', 'female', 'Passport', '12345', '5432154321', '', '', '', '', '', '', '', '', 0, 'Permanent', 'Lok Vihar', '', 'Achalpur', '', '', 'Maharashtra', 'IN', 0, 0, 12345, '', 1, 'Pitam Pura', 'New Delhi', '12', 1, '1', 1, '', '', 0, '', '0000-00-00 00:00:00', '', '', '', '', 0, '', '', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'self', 0, 'active'),
(2, 42, '', '', '', '', '', '', '', '', '', '', '', 'Gurgaon', '', 'graduate', 'School', 'School', 'BCA', 'IGNOU', 'PMP', 'DU', '', '', '2012-11-01', 'Principal distributor', 'female', 'UID', '123456', '5432154322', '', '', '', '', '', '', '', '', 0, 'owned', 'Lok Vihar', '', 'Achalpur', '', '', 'Maharashtra', 'IN', 110034, 0, 12345, '', 0, 'New Delhi', 'New Delhi', '1234', 12, 'Lok Vihar', 0, '', '', 0, '', '0000-00-00 00:00:00', '', '', '', '', 0, '', '', '', '', '', '0000-00-00 00:00:00', '2012-11-15 18:28:40', 'self', 0, 'active'),
(3, 45, '', '', '', '', '', '', '', '', '', '', '123', '345', '', '', 'SSB', 'SSB', 'BCOM', 'DU', 'MCOM', 'DU', '', '', '2013-12-09', '', 'female', 'Passport', '123', 'dsf4r44', '', '', '', '', '', '', '', '', 0, '', '', '', '', '', '', '', '', 0, 0, 0, '', 0, '', '', '', 0, '', 0, '', '', 0, '', '0000-00-00 00:00:00', '', '', '', '', 0, '', '', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'self', 0, 'active'),
(5, 43, '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '0000-00-00', '', '', '', '', '', '', '', '', '', '', '', '', '', 0, '', '', '', '', '', '', '', '', 0, 0, 0, '', 0, '', '', '', 0, '', 0, '', '', 0, '', '0000-00-00 00:00:00', '', '', '', '', 0, '', '', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'self', 0, 'active'),
(6, 49, '', '', '', '', '', '', '', '', '', '', 'D-280 Delhi', 'D-280 Delhi', 'unknown', '', '', '', '', '', '', '', '', '', '0000-00-00', '', 'male', '', '', '', '', '', '', '', '', '', '', '', 0, '', '', '', '', '', '', '', '', 0, 0, 0, '', 0, '', '', '', 0, '', 0, '', '', 0, '', '0000-00-00 00:00:00', '', '', '', '', 0, '', '', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'self', 0, 'active'),
(7, 50, '', '', '', '', '', '', '', '', '', '', '', 'Gurgaon', '', 'post graduate', 'Ramjas School', 'Ramjas School', 'BIS', 'DU', 'MCA', 'PTU', '', '', '0000-00-00', '', 'male', '', '', '', '', '', '', '', '', '', '', '', 0, '', '', '', '', '', '', '', '', 0, 0, 0, '', 0, '', '', '', 0, '', 0, '', '', 0, '', '0000-00-00 00:00:00', '', '', '', '', 0, '', '', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'self', 0, 'active'),
(8, 51, '', '', '', '', '', '', '', '', '', '', '', 'Gurgaon', '', 'post graduate', 'Ramjas School,RKP', 'Ramjas School', 'BIS', 'DU', 'MCA', 'PTU', '', '', '2012-11-01', '', 'female', 'Passport', '12345678', '1234567890', '', '', '', '', '', '', '', '', 0, '', '', '', '', '', '', '', '', 0, 0, 0, '', 0, '', '', '', 0, '', 0, '', '', 0, '', '0000-00-00 00:00:00', '', '', '', '', 0, '', '', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'self', 0, 'active'),
(9, 52, '', '', '', '', '', '', '', '', '', '', '', 'Gurgaon', 'NYC,USA', '', '', '', '', '', '', '', '', '', '0000-00-00', '', 'male', '', '', '', '', '', '', '', '', '', '', '', 0, '', '', '', '', '', '', '', '', 0, 0, 0, '', 0, '', '', '', 0, '', 0, '', '', 0, '', '0000-00-00 00:00:00', '', '', '', '', 0, '', '', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'self', 0, 'active'),
(11, 54, '', 'Mr', '', '', '', '', '', '', '', '', '', 'Gurgaon', '', '', '', '', '', '', '', '', '', '', '0000-00-00', '', 'male', '', '', '', '', '', '', '', '', '', '', '', 0, '', '', '', '', '', '', '', '', 0, 0, 0, '', 0, '', '', '', 0, '', 0, '', '', 0, '', '0000-00-00 00:00:00', '', '', '', '', 0, '', '', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'self', 0, 'active'),
(12, 55, '', 'Mr', '', '', '', '', '', '', '', '', '', 'Gurgaon', '', '', '', '', '', '', '', '', '', '', '0000-00-00', '', 'male', '', '', '', '', '', '', '', '', '', '', '', 0, '', '', '', '', '', '', '', '', 0, 0, 0, '', 0, '', '', '', 0, '', 0, '', '', 0, '', '0000-00-00 00:00:00', '', '', '', '', 0, '', '', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'self', 0, 'active'),
(13, 56, '', 'Mr', '', '', '', '', '', '', '', '', '', 'Gurgaon', '', '', '', '', '', '', '', '', '', '', '0000-00-00', '', 'male', '', '', '', '', '', '', '', '', '', '', '', 0, '', '', '', '', '', '', '', '', 0, 0, 0, '', 0, '', '', '', 0, '', 0, '', '', 0, '', '0000-00-00 00:00:00', '', '', '', '', 0, '', '', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'self', 0, 'active'),
(14, 57, '', 'Mr', '', '', '', '', '', '', '', '', '', 'Gurgaon', '', '', '', '', '', '', '', '', '', '', '0000-00-00', '', 'male', '', '', '', '', '', '', '', '', '', '', '', 0, '', '', '', '', '', '', '', '', 0, 0, 0, '', 0, '', '', '', 0, '', 0, '', '', 0, '', '0000-00-00 00:00:00', '', '', '', '', 0, '', '', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'self', 0, 'active'),
(15, 53, '', 'Mrs', '', '', '', '', '', '', '', '', '', 'Gurgaon', '', '', '', '', '', '', '', '', '', '', '0000-00-00', '', 'male', '', '', '', '', '', '', '', '', '', '', '', 0, '', '', '', '', '', '', '', '', 0, 0, 0, '', 0, '', '', '', 0, '', 0, '', '', 0, '', '0000-00-00 00:00:00', '', '', '', '', 0, '', '', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'self', 0, 'active'),
(16, 61, '112233', 'Mr', '', '', '', '', '', '', '', '', '', 'Gurgaon', '', 'post graduate', 'Ramjas School', 'Ramjas School', 'BIS', 'DU', 'MCA', 'PTU', '', '', '2012-11-01', '', 'female', 'Passport', '12345677', '8765432112', '', '', '', '', '', '', '', '', 0, 'owned house', 'Lok Vihar', '', '', '', '', 'Andaman and Nicobar Islands', 'IN', 110034, 0, 0, '', 0, '', '', '', 0, '', 0, '', '', 0, '', '0000-00-00 00:00:00', '', '', '', '', 0, '', '', '', '', '', '2012-11-14 00:00:00', '0000-00-00 00:00:00', 'ops', 0, 'active'),
(17, 63, '67890', 'Mr', '', '', '', '', '', '', '', '', '', 'Gurgaon', '', '', '', '', '', '', '', '', '', '', '0000-00-00', '', 'male', '', '', '', '', '', '', '', '', '', '', '', 0, '', '', '', '', '', '', '', '', 0, 0, 0, '', 0, '', '', '', 0, '', 0, '', '', 0, '', '0000-00-00 00:00:00', '', '', '', '', 0, '', '', '', '', '', '2012-11-14 00:00:00', '0000-00-00 00:00:00', 'ops', 0, 'active'),
(18, 66, '765432', 'Mr', '', '', '', '', '', '', '', '', '', 'Gurgaon', '', '', '', '', '', '', '', '', '', '', '0000-00-00', '', 'male', '', '', '', '', '', '', '', '', '', '', '', 0, '', '', '', '', '', '', '', '', 0, 0, 0, '', 0, '', '', '', 0, '', 0, '', '', 0, '', '0000-00-00 00:00:00', '', '', '', '', 0, '', '', '', '', '', '2012-11-14 00:00:00', '0000-00-00 00:00:00', 'ops', 0, 'active'),
(19, 67, '99999', 'Mr', '', '', '', '', '', '', '', '', '', 'Gurgaon', '', '', '', '', '', '', '', '', '', '', '0000-00-00', '', 'male', '', '', '', '', '', '', '', '', '', '', '', 0, '', '', '', '', '', '', '', '', 0, 0, 0, '', 0, '', '', '', 0, '', 0, '', '', 0, '', '0000-00-00 00:00:00', '', '', '', '', 0, '', '', '', '', '', '2012-11-14 00:00:00', '0000-00-00 00:00:00', 'ops', 0, 'active'),
(20, 68, '445566', 'Mr', '', '', '', '', '', '', '', '', '', 'Gurgaon', '', '', '', '', '', '', '', '', '', '', '0000-00-00', '', 'male', '', '', '', '', '', '', '', '', '', '', '', 0, '', '', '', '', '', '', '', '', 0, 0, 0, '', 0, '', '', '', 0, '', 0, '', '', 0, '', '0000-00-00 00:00:00', '', '', '', '', 0, '', '', '', '', '', '2012-11-15 00:00:00', '0000-00-00 00:00:00', 'ops', 0, 'active'),
(21, 69, '445561', 'Mr', '', '', '', '', '', '', '', '', '', 'Gurgaon', '', '', '', '', '', '', '', '', '', '', '0000-00-00', '', 'male', '', '', '', '', '', '', '', '', '', '', '', 0, '', '', '', '', '', '', '', '', 0, 0, 0, '', 0, '', '', '', 0, '', 0, '', '', 0, '', '0000-00-00 00:00:00', '', '', '', '', 0, '', '', '', '', '', '2012-11-16 00:00:00', '0000-00-00 00:00:00', 'ops', 0, 'active'),
(22, 70, '334488', 'Mr', '', '', '', '', '', '', '', '', '', 'Gurgaon', '', 'post graduate', 'Ramjas School', 'Ramjas School', 'BIS', 'DU', 'MCA', 'PTU', '', '', '2012-11-01', 'By Agent', 'female', 'Passport', '121212121', '3232323235', '', '', '', '', '', '', '', '', 0, 'owned', 'Lok Vihar', '', '', '', '', 'Madhya Pradesh', 'IN', 110034, 0, 12345, '', 0, 'Pitam Pura', 'New Delhi', '12', 1, 'New Delhi', 0, '', '', 0, '', '0000-00-00 00:00:00', '', '', '', '', 0, '', '', '', '', '', '2012-11-19 00:00:00', '0000-00-00 00:00:00', 'ops', 0, 'active'),
(23, 71, 'AFN12345678', 'Mr', '', '', '', '', '', '', '', '', '', 'FF-12 Augusta Point, Gurgaon - 122001', 'FF-12 Augusta Point, Gurgaon - 122001', 'intermediate', 'SSC, New Delhi', 'SSC New Delhi', '', '', '', '', '', '', '1975-01-13', 'By Agent', 'male', 'Passport', 'ACE345345', 'ACE3453232', '', '', '', '', '', '', '', '', 0, 'rented', 'D-123, Asd lane', 'Saakinaka', 'Mumbai', '', 'Maharastra', 'Maharashtra', 'IN', 330011, 0, 2147483647, '', 0, 'Andheri', 'Mumbai', '12121293223', 0, 'Andheri', 0, '', '', 0, '', '0000-00-00 00:00:00', '', '', '', '', 0, '', '', '', '', '', '2012-11-19 00:00:00', '0000-00-00 00:00:00', 'ops', 0, 'active'),
(24, 72, '678905', 'Mr', '', '', '', '', '', '', '', '', '', 'Gurgaon', '', '', '', '', '', '', '', '', '', '', '0000-00-00', '', 'male', '', '', '', '', '', '', '', '', '', '', '', 0, '', '', '', '', '', '', '', '', 0, 0, 0, '', 0, '', '', '', 0, '', 0, '', '', 0, '', '0000-00-00 00:00:00', '', '', '', '', 0, '', '', '', '', '', '2012-11-19 00:00:00', '0000-00-00 00:00:00', 'ops', 0, 'active'),
(25, 73, '999999', 'Mr', '', '', '', '', '', '', '', '', '', 'Gurgaon', '', 'intermediate', 'Ramjas', 'Ramjas School', '', '', '', '', '', '', '0000-00-00', '', 'male', '', '', '', '', '', '', '', '', '', '', '', 0, '', '', '', '', '', '', '', '', 0, 0, 0, '', 0, '', '', '', 0, '', 0, '', '', 0, '', '0000-00-00 00:00:00', '', '', '', '', 0, '', '', '', '', '', '2012-11-19 00:00:00', '0000-00-00 00:00:00', 'ops', 0, 'active'),
(26, 74, 'AFN3456', 'Mr', '', '', '', '', '', '', '', '', '', 'Gurgaon', '', '', '', '', '', '', '', '', '', '', '0000-00-00', '', 'male', '', '', '', '', '', '', '', '', '', '', '', 0, '', '', '', '', '', '', '', '', 0, 0, 0, '', 0, '', '', '', 0, '', 0, '', '', 0, '', '0000-00-00 00:00:00', '', '', '', '', 0, '', '', '', '', '', '2012-11-19 00:00:00', '0000-00-00 00:00:00', 'ops', 0, 'active'),
(27, 75, '6789055', 'Mr', '', '', '', '', '', '', '', '', '', 'Gurgaon', '', '', '', '', '', '', '', '', '', '', '0000-00-00', '', 'male', '', '', '', '', '', '', '', '', '', '', '', 0, '', '', '', '', '', '', '', '', 0, 0, 0, '', 0, '', '', '', 0, '', 0, '', '', 0, '', '0000-00-00 00:00:00', '', '', '', '', 0, '', '', '', '', '', '2012-11-19 00:00:00', '0000-00-00 00:00:00', 'ops', 0, 'active'),
(28, 76, 'AFN9876', 'Mr', '', '', '', '', '', '', '', '', '', 'Gurgaon', '', '', '', '', '', '', '', '', '', '', '0000-00-00', '', 'male', '', '', '', '', '', '', '', '', '', '', '', 0, '', '', '', '', '', '', '', '', 0, 0, 0, '', 0, '', '', '', 0, '', 0, '', '', 0, '', '0000-00-00 00:00:00', '', '', '', '', 0, '', '', '', '', '', '2012-11-19 00:00:00', '0000-00-00 00:00:00', 'ops', 0, 'active'),
(30, 79, '78906', 'Mr', '', '', '', '', '', '', '', '', '', 'Gurgaon', '', 'intermediate', 'Ramjas', 'Ramjas School', '', '', '', '', '', '', '2012-11-01', '', 'male', 'Passport', '9876543', '1234567899', '', '', '', '', '', '', '', '', 0, '', '', '', '', '', '', '', '', 0, 0, 0, '', 0, '', '', '', 0, '', 0, '', '', 0, '', '0000-00-00 00:00:00', '', '', '', '', 0, '', '', '', '', '', '2012-11-19 00:00:00', '0000-00-00 00:00:00', 'ops', 0, 'active'),
(31, 80, 'AFN12345699', 'Mr', '', '', '', '', '', '', '', '', '', 'FF-12 Augusta Point, Gurgaon - 122001', 'FF-12 Augusta Point, Gurgaon - 122001', 'intermediate', 'SSC', 'SSC', '', '', '', '', '', '', '1968-08-01', 'by agent', 'male', 'Passport', '1234256123', 'ACE3453233', '', '', '', '', '', '', '', '', 0, 'rented', 'Saakinaka, kholi no. 20', 'Saakinaka, Andheri', 'Mumbai', '', '', 'Maharashtra', 'IN', 222333, 0, 123456789, '', 0, 'adsfadsf', 'aesfsadfasdf', 'sadfassadf', 0, 'asdf', 0, '', '', 0, '', '0000-00-00 00:00:00', '', '', '', '', 0, '', '', '', '', '', '2012-11-19 00:00:00', '0000-00-00 00:00:00', 'ops', 0, 'active'),
(32, 81, 'AFN345645', 'Mr', '', '', '', '', '', '', '', '', '', 'Gurgaon', '', '', '', '', '', '', '', '', '', '', '0000-00-00', '', 'male', '', '', '', '', '', '', '', '', '', '', '', 0, '', '', '', '', '', '', '', '', 0, 0, 0, '', 0, '', '', '', 0, '', 0, '', '', 0, '', '0000-00-00 00:00:00', '', '', '', '', 0, '', '', '', '', '', '2012-11-19 00:00:00', '0000-00-00 00:00:00', 'ops', 0, 'active'),
(33, 82, '123123', 'Mr', '', '', '', '', '', '', '', '', '', 'Transerv', '', 'post graduate', 'CBSE', 'CBSE', '', '', '', '', '', '', '0000-00-00', '', 'male', '', '', '', '', '', '', '', '', '', '', '', 0, '', '', '', '', '', '', '', '', 0, 0, 0, '', 0, '', '', '', 0, '', 0, '', '', 0, '', '0000-00-00 00:00:00', '', '', '', '', 0, '', '', '', '', '', '2012-11-19 00:00:00', '0000-00-00 00:00:00', 'ops', 0, 'active'),
(34, 83, 'AF9876', 'Mr', '', '', '', '', '', '', '', '', '', 'Sakinaka, Mumbai, MH', '', 'graduate', 'HC Collage', 'CBSE', 'BBA', 'HC Collage', '', '', '', '', '1978-03-13', 'by agent', 'male', 'Passport', 'BA987656', 'ASD3453454', '', '', '', '', '', '', '', '', 0, 'owned', 'ABC', '', 'Mumbai', 'MH', 'Andheri', 'Maharashtra', 'IN', 220000, 0, 2147483647, '', 0, 'Andheri', 'Mumbai', 'SBI8934', 876, 'Andheri', 0, '', '', 0, '', '0000-00-00 00:00:00', '', '', '', '', 0, '', '', '', '', '', '2012-11-19 00:00:00', '0000-00-00 00:00:00', 'ops', 0, 'active'),
(35, 0, 'AFN5555', 'Mr', '', '', '', '', '', '', '', '', '', 'Gurgaon', '', '', '', '', '', '', '', '', '', '', '0000-00-00', '', 'male', '', '', '', '', '', '', '', '', '', '', '', 0, '', '', '', '', '', '', '', '', 0, 0, 0, '', 0, '', '', '', 0, '', 0, '', '', 0, '', '0000-00-00 00:00:00', '', '', '', '', 0, '', '', '', '', '', '2012-11-20 00:00:00', '0000-00-00 00:00:00', 'ops', 0, 'active'),
(36, 0, 'AFN55557', 'Mr', '', '', '', '', '', '', '', '', '', 'Gurgaon', '', '', '', '', '', '', '', '', '', '', '0000-00-00', '', 'male', '', '', '', '', '', '', '', '', '', '', '', 0, '', '', '', '', '', '', '', '', 0, 0, 0, '', 0, '', '', '', 0, '', 0, '', '', 0, '', '0000-00-00 00:00:00', '', '', '', '', 0, '', '', '', '', '', '2012-11-20 00:00:00', '0000-00-00 00:00:00', 'ops', 0, 'active'),
(37, 86, 'AFN555578', 'Mr', '', '', '', '', '', '', '', '', '', 'Gurgaon', '', 'post graduate', 'Ramjas School', 'Ramjas School', 'BIS', 'DU', 'MCA', 'PTU', '', '', '1982-12-27', 'by agent', 'female', 'Passport', 'ABC124BD', 'AISP12239S', '', '', '', '', '', '', '', '', 0, 'owned', 'Lok Vihar', '', 'Ahmednagar', '', '', 'Maharashtra', 'IN', 110034, 0, 12345, '', 0, 'Pitam Pura', 'New Delhi', '1234', 12, 'New Delhi', 0, '', '', 0, '', '0000-00-00 00:00:00', '', '', '', '', 0, '', '', '', '', '', '2012-11-20 00:00:00', '0000-00-00 00:00:00', 'ops', 0, 'active'),
(38, 87, 'AFN55778', 'Mr', '', '', '', '', '', '', '', '', '', 'Gurgaon', '', 'intermediate', 'Ramjas School', 'Ramjas School,RKP', '', '', '', '', '', '', '1982-11-01', 'by agent', 'female', 'Passport', 'ABC23456B', 'ABND1234BF', '', '', '', '', '', '', '', '', 0, 'owned', 'Lok Vihar', '', 'Ahmednagar', '', '', 'Maharashtra', 'IN', 110034, 0, 12345, '', 0, 'Pitam Pura', 'New Delhi', '1234', 12, 'New Delhi', 0, '', '', 0, '', '0000-00-00 00:00:00', '', '', '', '', 0, '', '', '', '', '', '2012-11-20 00:00:00', '0000-00-00 00:00:00', 'ops', 0, 'active'),
(39, 88, 'AFN4444', 'Mr', '', '', '', '', '', '', '', '', '', 'Gurgaon', '', 'intermediate', 'Ramjas School', 'Ramjas School', '', '', '', '', '', '', '1982-01-01', '', 'female', 'Passport', 'DEG12345', 'ABCDEF1234', '', '', '', '', '', '', '', '', 0, '', '', '', '', '', '', '', '', 0, 0, 0, '', 0, '', '', '', 0, '', 0, '', '', 0, '', '0000-00-00 00:00:00', '', '', '', '', 0, '', '', '', '', '', '2012-11-20 00:00:00', '0000-00-00 00:00:00', 'agent', 0, 'active'),
(40, 89, 'afn090909', 'Mr', '', '', '', '', '', '', '', '', '', '454/45', 'shop1', 'graduate', 'Ramjas', 'Ramjas', 'Btech', 'Ramjas', '', '', '', '', '0000-00-00', '', 'male', '', '', '', '', '', '', '', '', '', '', '', 0, '', '', '', '', '', '', '', '', 0, 0, 0, '', 0, '', '', '', 0, '', 0, '', '', 0, '', '0000-00-00 00:00:00', '', '', '', '', 0, '', '', '', '', '', '2012-11-22 00:00:00', '0000-00-00 00:00:00', 'ops', 0, 'active'),
(41, 90, 'AFN676767', 'Mr', '', '', '', '', '', '', '', '', '', 'Gurgaon', '', 'graduate', 'Ramjas School', 'Ramjas School', 'BIS', 'DU', '', '', '', '', '1982-12-01', 'by agent', 'female', 'Passport', 'AIP344647', 'BBI7464890', '', '', '', '', '', '', '', '', 0, 'owned', 'Lok Vihar', '', 'Ahmedpur', '', '', 'Maharashtra', 'IN', 110034, 0, 12345, '', 0, 'Pitam Pura', 'New Delhi', '1234', 12, 'New Delhi', 0, '', '', 0, '', '0000-00-00 00:00:00', '', '', '', '', 0, '', '', '', '', '', '2012-11-22 00:00:00', '0000-00-00 00:00:00', 'ops', 0, 'active'),
(42, 91, '224466', 'Mr', '', '', '', '', '', '', '', '', '', 'Gurgaon', '', 'intermediate', 'Ramjas School', 'Ramjas School', '', '', '', '', '', '', '1982-12-27', 'by agent', 'female', 'Passport', 'ABD1243561', 'DEFGH54322', '', '', '', '', '', '', '', '', 0, 'owned', 'Lok Vihar', '', '', '', '', 'Bangladesh', 'IN', 110034, 0, 12345, '', 0, 'Pitam Pura', 'New Delhi', '12', 12, 'New Delhi', 0, '', '', 0, '', '0000-00-00 00:00:00', '', '', '', '', 0, '', '', '', '', '', '2012-11-22 00:00:00', '0000-00-00 00:00:00', 'agent', 0, 'active'),
(43, 92, 'AFN123678', 'Mr', '', '', '', '', '', '', '', '', '', 'Gurgaon', '', 'intermediate', 'Ramjas School', 'Ramjas School', '', '', '', '', '', '', '1982-12-27', '', 'female', 'Passport', 'DEF567678', 'KJL7690KI2', '', '', '', '', '', '', '', '', 0, 'owned', 'Lok Vihar', '', 'Akkalkot', '', '', 'Maharashtra', 'IN', 110034, 0, 0, '', 0, '', '', '', 0, '', 0, '', '', 0, '', '0000-00-00 00:00:00', '', '', '', '', 0, '', '', '', '', '', '2012-11-22 00:00:00', '0000-00-00 00:00:00', 'agent', 0, 'active');

-- --------------------------------------------------------

--
-- Table structure for table `t_agent_fee_limit`
--

CREATE TABLE IF NOT EXISTS `t_agent_fee_limit` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `agent_id` int(11) NOT NULL,
  `currency` char(3) NOT NULL,
  `limit_out_max_daily` decimal(9,4) NOT NULL,
  `limit_out_max_monthly` decimal(9,4) NOT NULL,
  `limit_out_max_yearly` decimal(9,4) NOT NULL,
  `limit_out_min_txn` decimal(9,4) NOT NULL,
  `limit_out_max_txn` decimal(9,4) NOT NULL,
  `date_start` date NOT NULL,
  `date_end` date NOT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'inactive',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=18 ;

--
-- Dumping data for table `t_agent_fee_limit`
--

INSERT INTO `t_agent_fee_limit` (`id`, `agent_id`, `currency`, `limit_out_max_daily`, `limit_out_max_monthly`, `limit_out_max_yearly`, `limit_out_min_txn`, `limit_out_max_txn`, `date_start`, `date_end`, `status`) VALUES
(1, 1, 'INR', 10000.0000, 0.0000, 0.0000, 100.0000, 3000.0000, '2012-11-16', '0000-00-00', 'active'),
(2, 2, 'INR', 10000.0000, 0.0000, 0.0000, 0.0000, 0.0000, '2012-11-15', '0000-00-00', 'active'),
(7, 4, 'INR', 200.0000, 0.0000, 0.0000, 45.0000, 12.0000, '2012-11-14', '2012-11-16', 'inactive'),
(9, 5, 'INR', 10000.0000, 0.0000, 0.0000, 200.0000, 10000.0000, '2012-11-19', '0000-00-00', 'active'),
(10, 3, 'INR', 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, '2012-11-20', '2012-11-19', 'inactive'),
(11, 3, 'INR', 0.0000, 0.0000, 0.0000, 50.0000, 120.0000, '2012-11-20', '0000-00-00', 'active'),
(12, 6, 'INR', 2000.0000, 0.0000, 0.0000, 100.0000, 4000.0000, '2012-11-20', '0000-00-00', 'active'),
(13, 7, 'INR', 10000.0000, 0.0000, 0.0000, 1000.0000, 5000.0000, '2012-11-20', '0000-00-00', 'active'),
(14, 8, 'INR', 10000.0000, 0.0000, 0.0000, 200.0000, 10000.0000, '2012-11-20', '2012-11-19', 'inactive'),
(15, 8, 'INR', 10000.0000, 0.0000, 0.0000, 200.0000, 2500.0000, '2012-11-20', '2012-11-21', 'inactive'),
(16, 8, 'INR', 10000.0000, 0.0000, 0.0000, 200.0000, 4000.0000, '2012-11-22', '0000-00-00', 'active'),
(17, 9, 'INR', 10000.0000, 0.0000, 0.0000, 200.0000, 3000.0000, '2012-12-01', '0000-00-00', 'active');

-- --------------------------------------------------------

--
-- Table structure for table `t_agent_products`
--

CREATE TABLE IF NOT EXISTS `t_agent_products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `agent_id` int(11) NOT NULL,
  `fee_id` int(11) NOT NULL,
  `operation_id` int(11) NOT NULL,
  `date_modified` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_agent_idap` (`agent_id`) USING BTREE,
  KEY `fk_fee_idap` (`fee_id`) USING BTREE
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=24 ;

--
-- Dumping data for table `t_agent_products`
--

INSERT INTO `t_agent_products` (`id`, `agent_id`, `fee_id`, `operation_id`, `date_modified`) VALUES
(4, 41, 1, 5, '2012-11-08 10:52:34'),
(6, 41, 3, 5, '2012-11-08 11:03:45'),
(8, 49, 1, 1, '2012-11-08 12:23:21'),
(11, 51, 1, 5, '2012-11-08 15:43:23'),
(14, 44, 1, 5, '2012-11-09 18:32:09'),
(16, 42, 1, 5, '2012-11-09 19:25:42'),
(18, 53, 1, 5, '2012-11-15 19:47:57'),
(20, 43, 1, 5, '0000-00-00 00:00:00'),
(21, 71, 5, 9, '2012-11-19 12:58:57'),
(22, 80, 7, 10, '2012-11-19 15:34:11'),
(23, 83, 8, 9, '2012-11-19 18:18:44');

-- --------------------------------------------------------

--
-- Table structure for table `t_agent_products_log`
--

CREATE TABLE IF NOT EXISTS `t_agent_products_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `agent_id` int(11) NOT NULL,
  `fee_id` int(11) NOT NULL,
  `operation_id` int(11) NOT NULL,
  `date_modified` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_agent_idap` (`agent_id`) USING BTREE,
  KEY `fk_fee_idap` (`fee_id`) USING BTREE
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=15 ;

--
-- Dumping data for table `t_agent_products_log`
--

INSERT INTO `t_agent_products_log` (`id`, `agent_id`, `fee_id`, `operation_id`, `date_modified`) VALUES
(2, 41, 1, 5, '2012-11-08 10:24:21'),
(3, 41, 2, 5, '2012-11-08 10:24:39'),
(4, 41, 2, 5, '2012-11-08 10:41:30'),
(5, 41, 1, 5, '2012-11-08 10:44:18'),
(6, 41, 2, 5, '2012-11-08 10:44:37'),
(7, 41, 2, 5, '2012-11-08 10:42:02'),
(8, 41, 2, 5, '2012-11-08 10:51:45'),
(9, 41, 1, 5, '2012-11-08 10:52:25'),
(10, 41, 1, 5, '2012-11-08 10:52:05'),
(11, 49, 1, 1, '2012-11-08 12:21:57'),
(12, 44, 1, 5, '2012-11-09 18:31:48'),
(13, 51, 1, 5, '2012-11-09 18:32:40'),
(14, 61, 1, 5, '2012-11-16 15:44:09');

-- --------------------------------------------------------

--
-- Table structure for table `t_agent_transactions`
--

CREATE TABLE IF NOT EXISTS `t_agent_transactions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `agent_id` int(11) NOT NULL,
  `operation_id` int(11) NOT NULL,
  `amount` decimal(9,4) NOT NULL,
  `mode` enum('cr','dr') NOT NULL DEFAULT 'cr',
  `trans_type` varchar(20) DEFAULT NULL,
  `date_created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  KEY `fk_agent_idat` (`agent_id`) USING BTREE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=129 ;

--
-- Dumping data for table `t_agent_transactions`
--

INSERT INTO `t_agent_transactions` (`id`, `agent_id`, `operation_id`, `amount`, `mode`, `trans_type`, `date_created`) VALUES
(3, 43, 3, 200.0000, 'cr', NULL, '2012-11-07 11:02:43'),
(4, 43, 3, 500.0000, 'cr', NULL, '2012-11-07 11:02:55'),
(5, 43, 3, 10.0000, 'cr', NULL, '2012-11-07 11:07:06'),
(60, 43, 3, 100.0000, 'cr', NULL, '2012-11-07 17:16:49'),
(61, 43, 3, 300.0000, 'cr', NULL, '2012-11-07 17:23:29'),
(63, 43, 3, 200.0000, 'cr', NULL, '2012-11-07 17:35:21'),
(64, 43, 3, 100.5000, 'cr', NULL, '2012-11-07 17:38:40'),
(65, 43, 3, 50.0000, 'cr', NULL, '2012-11-07 17:39:09'),
(66, 43, 3, 20.0000, 'cr', NULL, '2012-11-07 17:40:01'),
(67, 43, 3, 130.0000, 'cr', NULL, '2012-11-07 17:43:41'),
(68, 43, 3, 500.0000, 'cr', 'CDLD', '2012-11-07 19:33:24'),
(69, 45, 3, 500.0000, 'cr', 'CDLD', '2012-11-08 11:19:53'),
(70, 43, 3, 500.0000, 'cr', 'CDLD', '2012-11-08 11:38:28'),
(71, 42, 1, 500.0000, 'cr', 'CDLD', '2012-11-08 12:26:40'),
(72, 50, 1, 234.0000, 'cr', 'CDLD', '2012-11-08 13:20:41'),
(73, 50, 1, 500.0000, 'cr', 'CDLD', '2012-11-08 13:28:09'),
(74, 43, 3, 500.0000, 'cr', 'CDLD', '2012-11-08 13:32:40'),
(75, 50, 1, 500.0000, 'cr', 'CDLD', '2012-11-08 13:59:37'),
(76, 50, 1, 1200.0000, 'cr', 'CDLD', '2012-11-08 14:01:39'),
(77, 50, 1, 2200.0000, 'cr', 'CDLD', '2012-11-08 14:02:20'),
(78, 50, 1, 100.0000, 'cr', 'CDLD', '2012-11-08 14:03:56'),
(79, 50, 1, 1230.0000, 'cr', 'CDLD', '2012-11-08 14:04:43'),
(80, 51, 1, 1234.0000, 'cr', 'CDLD', '2012-11-08 14:05:59'),
(81, 51, 1, 100.0000, 'cr', 'CDLD', '2012-11-08 14:06:21'),
(82, 43, 3, 500.0000, 'cr', 'CDLD', '2012-11-08 21:06:55'),
(83, 43, 3, 200.0000, 'cr', 'CDLD', '2012-11-09 11:18:44'),
(84, 43, 3, 200.0000, 'cr', 'CDLD', '2012-11-09 17:31:43'),
(85, 43, 3, 200.0000, 'cr', 'CDLD', '2012-11-09 18:08:39'),
(86, 43, 3, 200.0000, 'cr', 'CDLD', '2012-11-09 18:11:00'),
(87, 42, 5, 1000.0000, 'cr', 'CDLD', '2012-11-09 19:29:33'),
(88, 43, 0, 100.0000, 'dr', 'CDRG', '2012-11-17 13:33:06'),
(89, 43, 0, 100.0000, 'dr', 'CDRG', '2012-11-17 13:33:23'),
(90, 43, 0, 100.0000, 'dr', 'CDRG', '2012-11-17 13:40:49'),
(91, 43, 0, 100.0000, 'dr', 'CDRG', '2012-11-17 13:40:50'),
(92, 43, 0, 100.0000, 'dr', 'CDRG', '2012-11-17 13:41:03'),
(93, 43, 0, 100.0000, 'dr', 'CDRG', '2012-11-17 13:41:42'),
(94, 43, 0, 100.0000, 'dr', 'CDRG', '2012-11-19 13:04:18'),
(95, 43, 0, 1200.0000, 'dr', 'CDRG', '2012-11-19 13:18:24'),
(96, 43, 0, 1000.0000, 'dr', 'CDRG', '2012-11-19 13:34:30'),
(97, 43, 0, 3000.0000, 'dr', 'CDRG', '2012-11-19 13:39:20'),
(98, 71, 9, 5000.0000, 'cr', 'CDLD', '2012-11-19 14:08:47'),
(99, 61, 1, 5000.0000, 'cr', 'CDLD', '2012-11-19 14:16:11'),
(100, 43, 0, 1000.0000, 'dr', 'CDRG', '2012-11-19 14:23:36'),
(101, 61, 1, 2000.0000, 'cr', 'CDLD', '2012-11-19 14:26:46'),
(102, 43, 9, 5000.0000, 'cr', 'CDLD', '2012-11-19 14:43:27'),
(103, 43, 0, 3000.0000, 'dr', 'CDRG', '2012-11-19 14:58:02'),
(104, 80, 10, 2000.0000, 'cr', 'CDLD', '2012-11-19 15:35:51'),
(105, 43, 0, 2000.0000, 'dr', 'CDRG', '2012-11-19 15:46:08'),
(106, 43, 0, 1000.0000, 'dr', 'CDRG', '2012-11-19 15:55:34'),
(107, 81, 9, 5000.0000, 'cr', 'CDLD', '2012-11-19 16:27:31'),
(108, 43, 0, 1000.0000, 'dr', 'CDRG', '2012-11-19 16:30:59'),
(109, 83, 9, 10000.0000, 'cr', 'CDLD', '2012-11-19 18:24:01'),
(110, 71, 9, 1000.0000, 'cr', 'CDLD', '2012-11-19 18:39:06'),
(111, 83, 0, 500.0000, 'dr', 'CDRG', '2012-11-19 19:12:37'),
(112, 83, 9, 250.0000, 'cr', 'CDLD', '2012-11-19 19:14:35'),
(113, 43, 0, 2000.0000, 'dr', 'CDRG', '2012-11-20 14:18:07'),
(114, 42, 0, 1000.0000, 'dr', 'CDRG', '2012-11-21 13:01:02'),
(115, 43, 0, 1000.0000, 'dr', 'CDRG', '2012-11-21 15:13:25'),
(116, 43, 0, 2000.0000, 'dr', 'CDRG', '2012-11-21 18:32:27'),
(117, 43, 0, 1500.0000, 'dr', 'CDRG', '2012-11-21 19:49:33'),
(118, 43, 0, 2300.0000, 'dr', 'CDRG', '2012-11-22 12:31:31'),
(119, 43, 0, 2000.0000, 'dr', 'CDRG', '2012-11-22 14:16:39'),
(120, 43, 0, 1500.0000, 'dr', 'CDRG', '2012-11-22 15:17:05'),
(121, 43, 0, 1200.0000, 'dr', 'CDRG', '2012-11-22 15:38:09'),
(122, 43, 0, 1500.0000, 'dr', 'CDRG', '2012-11-22 15:47:16'),
(123, 43, 0, 1500.0000, 'dr', 'CDRG', '2012-11-22 17:32:52'),
(124, 43, 0, 1500.0000, 'dr', 'CDRG', '2012-11-23 11:25:04'),
(125, 43, 3, 200.0000, 'cr', 'CDLD', '2012-11-23 11:57:57'),
(126, 43, 3, 200.0000, 'cr', 'CDLD', '2012-11-23 11:58:50'),
(127, 43, 0, 1000.0000, 'dr', 'CDRG', '2012-11-23 12:28:01'),
(128, 43, 0, 1200.0000, 'dr', 'CDRG', '2012-11-23 15:31:18');

-- --------------------------------------------------------

--
-- Table structure for table `t_api_user`
--

CREATE TABLE IF NOT EXISTS `t_api_user` (
  `session_id` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(50) NOT NULL,
  `allowed_ip` varchar(15) NOT NULL,
  `updated_on` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `t_bank`
--

CREATE TABLE IF NOT EXISTS `t_bank` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `swift_code` varchar(11) NOT NULL,
  `city` varchar(50) NOT NULL,
  `branch_name` varchar(50) NOT NULL,
  `address` varchar(250) NOT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=10 ;

--
-- Dumping data for table `t_bank`
--

INSERT INTO `t_bank` (`id`, `name`, `swift_code`, `city`, `branch_name`, `address`, `status`) VALUES
(1, 'Axis Bank', 'AI98SGPE', 'Mumbai', 'Bandra', 'A-201, Bandra, Mumbai', 'active'),
(2, 'Bank of India', 'AI98SGBB', 'New Delhi', 'Parliament Street', 'A-202, Parliament Street, New Delhi', 'active'),
(3, 'ICICI Bank', 'ICICI', 'Mumbai', 'mumbai central', 'asdf', 'active'),
(4, 'HDFC Bank', 'SDKLFJOWEIO', 'GURGAON', 'SECTOR-53', 'VATICA ATTRIUM', 'active'),
(5, 'HSBC BANK', 'HSBC9048W', 'GURGAON', 'DLF-2', 'DLF-2, MG Road', 'active'),
(6, 'YES Bank', 'YES', 'Mumbai', 'Andheri', 'asdf, saakinakdda,mumbai, maharashtra', 'active'),
(7, 'SBI Bank', 'SBI8765439', 'Mumbai', 'Andheri', 'Sakinaka', 'active'),
(8, 'Barclays', 'BARCLAY', 'New Delhi', 'Pitam Pura', 'Lok Vikar, Pitam Pura, New Delhi', 'active');

-- --------------------------------------------------------

--
-- Table structure for table `t_cardholders`
--

CREATE TABLE IF NOT EXISTS `t_cardholders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `crn` varchar(30) CHARACTER SET utf8 DEFAULT NULL,
  `email` varchar(50) CHARACTER SET utf8 NOT NULL,
  `title` enum('mr','mrs','ms','dr','prof') CHARACTER SET utf8 NOT NULL,
  `first_name` varchar(20) CHARACTER SET utf8 DEFAULT NULL,
  `middle_name` varchar(20) CHARACTER SET utf8 DEFAULT NULL,
  `last_name` varchar(20) CHARACTER SET utf8 DEFAULT NULL,
  `mobile_country_code` varchar(6) CHARACTER SET utf8 DEFAULT NULL,
  `mobile_number` varchar(15) CHARACTER SET utf8 DEFAULT NULL,
  `activation_id` int(11) NOT NULL,
  `activation_status` enum('sucess','failed','pending') NOT NULL,
  `enroll_status` enum('approved','pending') NOT NULL,
  `status` enum('blocked','unblocked') NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=175 ;

--
-- Dumping data for table `t_cardholders`
--

INSERT INTO `t_cardholders` (`id`, `product_id`, `crn`, `email`, `title`, `first_name`, `middle_name`, `last_name`, `mobile_country_code`, `mobile_number`, `activation_id`, `activation_status`, `enroll_status`, `status`) VALUES
(79, 1, 'crn00001', '', 'mr', 'ashish', '', 'vats', '+91', '1234567896', 0, 'sucess', 'approved', 'blocked'),
(80, 1, 'crn00002', '', 'mr', 'vivek', 'kumar', 'sharma', '+91', '9810236548', 0, 'sucess', 'approved', 'blocked'),
(81, 1, 'crn00003', '', 'mr', 'Robin', '', 'Saxena', '+91', '9810123456', 0, 'sucess', 'approved', 'blocked'),
(82, 1, 'crn00004', '', 'mr', 'Raj', '', 'kumar', '+91', '9885525612', 0, 'sucess', 'approved', 'blocked'),
(83, 2, 'crn00005', '', 'mr', 'Jit', '', 'kumar', '+91', '3434343456', 0, 'sucess', 'approved', 'blocked'),
(84, 2, 'crn00006', '', 'mr', 'peter', '', 'jha', '+91', '9810212347', 0, 'sucess', 'approved', 'blocked'),
(85, 1, 'crn00007', '', 'mr', 'Vikram', '', 'Singh', '+91', '9899191919', 0, 'sucess', 'approved', 'blocked'),
(86, 1, 'crn00008', '', 'mr', 'Manoj', '', 'Kumar', '+91', '9885525369', 0, 'sucess', 'approved', 'blocked'),
(88, 1, 'crn00010', '', 'mr', 'Vijay', '', 'Singh', '+91', '9810112398', 0, 'sucess', 'approved', 'blocked'),
(89, 1, 'crn00011', '', 'mr', 'Raj', '', 'Kumar', '+91', '9875556985', 0, 'sucess', 'approved', 'blocked'),
(90, 3, 'crn00012', '', 'mr', 'neeta', '', 'saini', '+91', '9811112345', 0, 'sucess', 'approved', 'blocked'),
(91, 3, 'crn00013', '', 'mr', 'Rohan', '', 'Kumar', '+91', '9885500123', 0, 'sucess', 'approved', 'blocked'),
(92, 1, 'crn00014', '', 'mr', 'jit', '', 'varish', '+91', '9880302058', 0, 'sucess', 'approved', 'blocked'),
(93, 3, 'crn00015', '', 'mr', 'Rajender', '', 'Kumar', '+91', '9866655501', 0, 'sucess', 'approved', 'blocked'),
(95, 3, 'crn00017', '', 'mr', 'Neeraj', '', 'Jha', '+91', '9810104078', 0, 'sucess', 'approved', 'blocked'),
(96, 3, 'crn00018', '', 'mr', 'Ashok', '', 'Jain', '+91', '9836997451', 0, 'sucess', 'approved', 'blocked'),
(97, 3, 'crn00019', '', 'mr', 'Ram', '', 'Prakash', '+91', '9923569847', 0, 'sucess', 'approved', 'blocked'),
(98, 3, 'crn00020', '', 'prof', 'Ram', '', 'Prakash', '+91', '9811112340', 0, 'sucess', 'approved', 'blocked'),
(99, 3, 'crn00021', '', 'mr', 'Raj', '', 'Anand', '+91', '9810317882', 0, 'sucess', 'approved', 'blocked'),
(100, 3, 'crn00022', '', 'mr', 'ashish', '', 'Prakash', '+91', '3434341234', 0, 'sucess', 'approved', 'blocked'),
(101, 3, 'crn00023', 'test@test.com', 'dr', 'Ramneek', '', 'Kumar', '+91', '9885521234', 0, 'sucess', 'approved', 'blocked'),
(105, 1, 'crn00027', 'robin8@test.com', 'mr', 'Manoj', '', 'Prakash', '+91', '3434120000', 0, 'sucess', 'approved', 'blocked'),
(106, 3, 'crn00028', 'ashish4444@transerv.co.in', 'mr', 'Ashish', '', 'Vats', '+91', '9712345698', 0, 'sucess', 'approved', 'blocked'),
(107, 3, 'crn00029', 'robin9@test.com', 'dr', 'Raj', '', 'Prakash', '+91', '9632145872', 0, 'sucess', 'approved', 'blocked'),
(108, 3, 'crn00030', 'ashi@transerv.co.in', 'mr', 'ashish', '', 'vats', '+91', '9512345698', 0, 'sucess', 'approved', 'blocked'),
(109, 3, 'crn00031', 'anand@anand.com', 'mr', 'ashish', '', 'vats', '+91', '9815515455', 0, 'sucess', 'approved', 'blocked'),
(110, 3, 'crn00032', 'adit@test.com', 'mr', 'Adit', '', 'Jain', '+91', '9878896989', 0, 'sucess', 'approved', 'blocked'),
(111, 3, 'crn00033', 'ashish12344@transerv.co.in', 'mr', 'Ashish', '', 'vats', '+91', '9810123567', 0, 'sucess', 'approved', 'blocked'),
(112, 1, 'crn00034', 'robin3333@test.com', 'mr', 'Ram', '', 'Kumar', '+91', '3434343466', 0, 'sucess', 'approved', 'blocked'),
(113, 3, 'crn00035', 'ash@transerv.co.in', 'mr', 'Ash', '', 'Jain', '+91', '9811228899', 0, 'sucess', 'approved', 'blocked'),
(130, 10, 'crn00073', 'neeraj11@test.com', 'mr', 'Neeraj', '', 'Kumar', '+91', '9711198545', 0, 'pending', 'pending', 'unblocked'),
(131, 1, 'crn00089', 'neerajdfh@test.com', 'mr', 'Abhishek', '', 'Bisht', '+91', '1234551236', 0, 'pending', 'pending', 'unblocked'),
(132, 1, 'crn00097', 'teat2@fdfs.com', 'mr', 'akash', '', 'jain', '+91', '3654123323', 0, 'pending', 'pending', 'unblocked'),
(133, 1, 'crn000102', 'teatdf2@fdfs.com', 'mr', 'aa', '', 'fsdf', '+91', '3654123312', 0, 'pending', 'pending', 'unblocked'),
(134, 1, 'crn000108', 'teatsss2@fdfs.com', 'mr', 'akash', '', 'jain', '+91', '1236547895', 0, 'pending', 'pending', 'unblocked'),
(135, 1, 'crn000114', 'neeleshdsfas@test.com', 'mr', 'Neelesh', '', 'Anand', '+91', '1236547777', 0, 'pending', 'pending', 'unblocked'),
(136, 1, 'crn000119', 'tedfat2@fdfs.com', 'mr', 'akash', '', 'Jain', '+91', '1236547987', 0, 'pending', 'pending', 'unblocked'),
(137, 1, 'crn000123', 'vikram0207@gmail.com', 'mr', 'Vikram', '', 'Singh', '+91', '1236555558', 0, 'pending', 'pending', 'unblocked'),
(139, 1, 'crn000127', 'jit@transerv.co.in', 'mr', 'Jit', '', 'Varish', '+91', '9800000000', 0, 'pending', 'pending', 'unblocked'),
(140, 1, 'crn000130', 'needdas@test.com', 'mr', 'akash', '', 'Varish', '+91', '3215487889', 0, 'pending', 'pending', 'unblocked'),
(141, 1, 'crn000133', 'ashdfasdfaish@transerv.co.in', 'mr', 'Neelesh', '', 'Anand', '+91', '2656897489', 0, 'sucess', 'approved', 'blocked'),
(142, 15, 'crn000137', 'anish@transerv.co.in', 'mr', 'Sandeep', '', 'Ghule', '+91', '9594781803', 0, 'pending', 'pending', 'unblocked'),
(143, 1, 'crn000140', 'ashishdsfasd@transerv.co.in', 'mr', 'akash', '', 'Varish', '+91', '1234567889', 0, 'pending', 'pending', 'unblocked'),
(144, 1, NULL, 'jitsdfasdf@trdfs.co.in', 'mr', 'akash', '', 'Varish', '+91', '1236598787', 0, 'pending', 'pending', 'unblocked'),
(145, 1, NULL, 'werews@test.com', 'mr', 'Rakesh', '', 'Kumar', '+91', '1236987456', 0, 'pending', 'pending', 'unblocked'),
(146, 1, NULL, 'asfa@fads.com', 'mr', 'kishan', '', 'Kumar', '+91', '1236454545', 0, 'pending', 'pending', 'unblocked'),
(147, 1, NULL, 'dasfa@fads.com', 'mr', 'Rakesh', '', 'Kumar', '+91', '1236543987', 0, 'pending', 'pending', 'unblocked'),
(148, 1, NULL, 'asfsa@fads.com', 'mr', 'akash', '', 'Varish', '+91', '1123366914', 0, 'pending', 'pending', 'unblocked'),
(149, 1, NULL, 'dfkasld@test.com', 'ms', 'Anu', '', 'Jha', '+91', '1236598784', 0, 'pending', 'pending', 'unblocked'),
(150, 1, 'crn000143', 'aniket@transerv.co.in', 'mr', 'aniket', 'shantaram', 'labde', '+91', '1236547896', 0, 'pending', 'pending', 'unblocked'),
(151, 1, 'crn000147', 'dfasdfa@fsda.com', 'mr', 'Rahul', '', 'Kumar', '+91', '2121212121', 0, 'pending', 'pending', 'unblocked'),
(152, 1, NULL, 'ashishdfdf@transerv.co.in', 'ms', 'neeta', '', 'Jain', '+91', '1235458788', 0, 'pending', 'pending', 'unblocked'),
(153, 1, 'crn000150', 'sdfsd@fsda.com', 'mr', 'Anuj', '', 'Khan', '+91', '1234568798', 0, 'pending', 'pending', 'unblocked'),
(154, 1, 'crn00073', 'sddfsd@fsda.com', 'ms', 'Anita', '', 'Puri', '+91', '3257888787', 0, 'pending', 'pending', 'unblocked'),
(155, 1, NULL, 'sddfsd@fsda.com', 'mr', 'Ashok', '', 'Pandey', '+91', '3257888787', 0, 'pending', 'pending', 'unblocked'),
(156, 1, 'crn00074', 'afasda@fasd.com', 'mr', 'Neelam', '', 'Jain', '+91', '9810780690', 0, 'pending', 'pending', 'unblocked'),
(157, 1, 'crn00081', 'fdga@garg.com', 'mrs', 'Anita', '', 'jain', '+91', '1265487874', 0, 'pending', 'pending', 'unblocked'),
(158, 1, 'crn00095', 'dshfhas@fasfa.com', 'ms', 'Neeta', '', 'Rathore', '+91', '1234567895', 0, 'pending', 'pending', 'unblocked'),
(159, 1, 'crn00097', 'ddfddfh@transerv.co.in', 'mr', 'Akash', '', 'Vats', '+91', '1233368578', 0, 'pending', 'pending', 'unblocked'),
(160, 1, 'crn00099', 'sdfa@fasd.com', 'ms', 'Reena', '', 'Sharma', '+91', '1221212121', 0, 'pending', 'pending', 'unblocked'),
(161, 1, NULL, 'raj@testdd.com', 'mr', 'Raj', '', 'Kumar', '+91', '1235689893', 0, 'pending', 'pending', 'unblocked'),
(162, 1, 'crn00153', 'asdfhsd@fdfd.com', 'mr', 'Anooj', '', 'Kumar', '+91', '1212121221', 0, 'pending', 'pending', 'unblocked'),
(163, 1, 'crn00154', 'ddsd@fdfd.com', 'mr', 'Anoop', '', 'Jain', '+91', '1212123333', 0, 'pending', 'pending', 'unblocked'),
(164, 1, NULL, 'shdfkjh@fasdfa.com', 'mr', 'Ashu', '', 'Jain', '+91', '1212121212', 0, 'pending', 'pending', 'unblocked'),
(165, 1, 'crn00158', 'akash@test-test.com', 'mr', 'Akash', '', 'Kumar', '+91', '1212121288', 0, 'pending', 'pending', 'unblocked'),
(166, 1, 'crn00161', 'tst@test.com', 'dr', 'Neelay', '', 'Anand', '+91', '2135566598', 0, 'pending', 'pending', 'unblocked'),
(167, 1, NULL, 'ramesh@test.com', 'mr', 'Ramesh', '', 'Kumar', '+91', '1212659878', 0, 'pending', 'pending', 'unblocked'),
(168, 1, 'crn00163', 'chayajohn@test.com', 'ms', 'Chaya', '', 'John', '+91', '1212698788', 0, 'pending', 'pending', 'unblocked'),
(169, 1, NULL, 'rameshjain@test.com', 'mr', 'Ramesh', '', 'Jain', '+91', '1212659871', 0, 'pending', 'pending', 'unblocked'),
(170, 1, 'crn00165', 'sunita@test.com', 'mrs', 'Sunita', '', 'jain', '+91', '1215454589', 0, 'pending', 'pending', 'unblocked'),
(171, 1, 'crn00168', 'Anoop@test.com', 'mr', 'Anoop', '', 'Jain', '+91', '1215432333', 0, 'pending', 'pending', 'unblocked'),
(172, 1, NULL, 'peter@test.com', 'mr', 'Peter', '', 'John', '+91', '1215432387', 0, 'pending', 'pending', 'unblocked'),
(173, 1, 'crn00171', 'dfasd@gda.com', 'mr', 'Ashish', '', 'Sharma', '+91', '1212121211', 0, 'pending', 'pending', 'unblocked'),
(174, 1, NULL, 'dfadsd@gda.com', 'mr', 'Ashish', '', 'Sharma', '+91', '1212122211', 0, 'pending', 'pending', 'unblocked');

-- --------------------------------------------------------

--
-- Table structure for table `t_cardholder_details`
--

CREATE TABLE IF NOT EXISTS `t_cardholder_details` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cardholder_id` int(11) NOT NULL,
  `arn` varchar(10) CHARACTER SET utf8 DEFAULT NULL,
  `alternate_contact_number` varchar(20) CHARACTER SET utf8 NOT NULL,
  `father_first_name` varchar(20) CHARACTER SET utf8 NOT NULL,
  `father_middle_name` varchar(20) CHARACTER SET utf8 NOT NULL,
  `father_last_name` varchar(20) CHARACTER SET utf8 NOT NULL,
  `mother_maiden_name` varchar(20) CHARACTER SET utf8 NOT NULL,
  `spouse_first_name` varchar(20) CHARACTER SET utf8 NOT NULL,
  `spouse_middle_name` varchar(20) CHARACTER SET utf8 NOT NULL,
  `spouse_last_name` varchar(20) CHARACTER SET utf8 NOT NULL,
  `res_type` enum('owned','rented','parental') CHARACTER SET utf8 NOT NULL,
  `nationality` varchar(30) CHARACTER SET utf8 NOT NULL,
  `date_of_birth` date NOT NULL,
  `gender` enum('male','female') CHARACTER SET utf8 NOT NULL,
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
  `customer_mvc_type` enum('mvcc','mvci') CHARACTER SET utf8 NOT NULL,
  `device_id` varchar(30) CHARACTER SET utf8 NOT NULL,
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
  `id_proof_attached` enum('yes','no') CHARACTER SET utf8 DEFAULT NULL,
  `address_proof_attached` enum('yes','no') CHARACTER SET utf8 DEFAULT NULL,
  `uid_number` varchar(30) CHARACTER SET utf8 DEFAULT NULL,
  `already_bank_account` enum('yes','no') CHARACTER SET utf8 DEFAULT NULL,
  `vehicle_type` varchar(20) CHARACTER SET utf8 DEFAULT NULL,
  `educational_qualifications` varchar(100) CHARACTER SET utf8 DEFAULT NULL,
  `family_members` int(4) DEFAULT NULL,
  `date_created` datetime DEFAULT NULL,
  `date_modified` datetime DEFAULT NULL,
  `date_activated` datetime DEFAULT NULL,
  `registration_id` int(11) NOT NULL,
  `registration_type` enum('self','ops','agent') NOT NULL,
  `shmart_rewards` enum('yes','no') NOT NULL,
  `products_acknowledgement` enum('1','0') DEFAULT NULL,
  `rewards_acknowledgement` enum('1','0') DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=162 ;

--
-- Dumping data for table `t_cardholder_details`
--

INSERT INTO `t_cardholder_details` (`id`, `cardholder_id`, `arn`, `alternate_contact_number`, `father_first_name`, `father_middle_name`, `father_last_name`, `mother_maiden_name`, `spouse_first_name`, `spouse_middle_name`, `spouse_last_name`, `res_type`, `nationality`, `date_of_birth`, `gender`, `flat_number`, `address_line1`, `address_line2`, `city`, `taluka`, `district`, `state`, `country`, `pincode`, `landmark`, `customer_mvc_type`, `device_id`, `caste_category`, `profession`, `annual_income`, `pan_number`, `nominee_first_name`, `nominee_middle_name`, `nominee_last_name`, `date_of_birth_nominee`, `relationship_with_applicant`, `declaration`, `place`, `introducer_first_name`, `introducer_middle_name`, `introducer_last_name`, `introducer_account_number`, `introducer_branch`, `introducer_know_since`, `id_proof_attached`, `address_proof_attached`, `uid_number`, `already_bank_account`, `vehicle_type`, `educational_qualifications`, `family_members`, `date_created`, `date_modified`, `date_activated`, `registration_id`, `registration_type`, `shmart_rewards`, `products_acknowledgement`, `rewards_acknowledgement`, `status`) VALUES
(1, 0, NULL, '', '', '', '', '', '', '', '', '', '', '0000-00-00', '', '', '', '', '', '', '', '', '', 0, '', 'mvcc', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2012-10-30 13:36:30', '2012-11-15 19:15:37', NULL, 0, 'self', '', NULL, NULL, 'active'),
(2, 0, NULL, '', '', '', '', '', '', '', '', '', '', '0000-00-00', '', '', '', '', '', '', '', '', '', 0, '', 'mvcc', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2012-10-30 13:43:48', '2012-11-15 19:15:37', NULL, 0, 'self', '', NULL, NULL, 'active'),
(3, 0, NULL, '', '', '', '', '', '', '', '', '', '', '0000-00-00', '', '', '', '', '', '', '', '', '', 0, '', 'mvcc', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2012-10-30 13:43:53', '2012-11-15 19:15:37', NULL, 0, 'self', '', NULL, NULL, 'active'),
(4, 0, NULL, '', '', '', '', '', '', '', '', '', '', '0000-00-00', '', '', '', '', '', '', '', '', '', 0, '', 'mvcc', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2012-10-30 13:56:02', '2012-11-15 19:15:37', NULL, 0, 'self', '', NULL, NULL, 'active'),
(5, 0, NULL, '', '', '', '', '', '', '', '', '', '', '0000-00-00', '', '', '', '', '', '', '', '', '', 0, '', 'mvcc', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2012-10-30 13:57:28', '2012-11-15 19:15:37', NULL, 0, 'self', '', NULL, NULL, 'active'),
(6, 0, NULL, '', '', '', '', '', '', '', '', '', '', '0000-00-00', '', '', '', '', '', '', '', '', '', 0, '', 'mvcc', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2012-10-30 14:00:14', '2012-11-15 19:15:37', NULL, 0, 'self', '', NULL, NULL, 'active'),
(7, 0, NULL, '', '', '', '', '', '', '', '', '', '', '0000-00-00', '', '', '', '', '', '', '', '', '', 0, '', 'mvcc', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2012-10-30 14:02:35', '2012-11-15 19:15:37', NULL, 0, 'self', '', NULL, NULL, 'active'),
(8, 0, NULL, '', '', '', '', '', '', '', '', '', '', '0000-00-00', '', '', '', '', '', '', '', '', '', 0, '', 'mvcc', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2012-10-30 14:03:24', '2012-11-15 19:15:37', NULL, 0, 'self', '', NULL, NULL, 'active'),
(9, 0, NULL, '', '', '', '', '', '', '', '', '', '', '0000-00-00', '', '', '', '', '', '', '', '', '', 0, '', 'mvcc', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2012-10-30 14:05:35', '2012-11-15 19:15:37', NULL, 0, 'self', '', NULL, NULL, 'active'),
(10, 0, NULL, '', '', '', '', '', '', '', '', '', '', '0000-00-00', '', '', '', '', '', '', '', '', '', 0, '', 'mvcc', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2012-10-30 14:06:23', '2012-11-15 19:15:37', NULL, 0, 'self', '', NULL, NULL, 'active'),
(11, 0, NULL, '', '', '', '', '', '', '', '', '', '', '0000-00-00', '', '', '', '', '', '', '', '', '', 0, '', 'mvcc', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2012-10-30 14:08:12', '2012-11-15 19:15:37', NULL, 0, 'self', '', NULL, NULL, 'active'),
(12, 0, NULL, '', '', '', '', '', '', '', '', '', '', '0000-00-00', '', '', '', '', '', '', '', '', '', 0, '', 'mvcc', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2012-10-30 14:08:46', '2012-11-15 19:15:37', NULL, 0, 'self', '', NULL, NULL, 'active'),
(13, 0, NULL, '', '', '', '', '', '', '', '', '', '', '0000-00-00', '', '', '', '', '', '', '', '', '', 0, '', 'mvcc', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2012-10-30 14:10:15', '2012-11-15 19:15:37', NULL, 0, 'self', '', NULL, NULL, 'active'),
(14, 0, NULL, '', '', '', '', '', '', '', '', '', '', '0000-00-00', '', '', '', '', '', '', '', '', '', 0, '', 'mvcc', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2012-10-30 14:11:05', '2012-11-15 19:15:37', NULL, 0, 'self', '', NULL, NULL, 'active'),
(15, 0, NULL, '', '', '', '', '', '', '', '', '', '', '0000-00-00', '', '', '', '', '', '', '', '', '', 0, '', 'mvcc', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2012-10-30 14:12:18', '2012-11-15 19:15:37', NULL, 0, 'self', '', NULL, NULL, 'active'),
(16, 0, NULL, '', '', '', '', '', '', '', '', '', '', '0000-00-00', '', '', '', '', '', '', '', '', '', 0, '', 'mvcc', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2012-10-30 14:13:28', '2012-11-15 19:15:37', NULL, 0, 'self', '', NULL, NULL, 'active'),
(17, 0, NULL, '', '', '', '', '', '', '', '', '', '', '0000-00-00', '', '', '', '', '', '', '', '', '', 0, '', 'mvcc', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2012-10-30 14:21:32', '2012-11-15 19:15:37', NULL, 0, 'self', '', NULL, NULL, 'active'),
(18, 0, NULL, '', '', '', '', '', '', '', '', '', '', '0000-00-00', '', '', '', '', '', '', '', '', '', 0, '', 'mvcc', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2012-10-30 14:23:17', '2012-11-15 19:15:37', NULL, 0, 'self', '', NULL, NULL, 'active'),
(19, 19, 'arn777', '', 'abc', 'abc2', 'abc3', 'mmn1', 'sp1', 'sp2', 'sp3', '', '', '0000-00-00', '', '', '', '', '', '', '', '', '', 0, '', 'mvcc', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2012-10-30 14:41:19', NULL, NULL, 0, 'self', 'yes', NULL, NULL, 'active'),
(20, 19, 'arn87878', '', 'sdf', 'sdf', 'sdf', 'sfd', 'sdf', 'sdf', 'sdf', '', '', '0000-00-00', '', '', '', '', '', '', '', '', '', 0, '', 'mvcc', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2012-10-30 14:42:39', NULL, NULL, 0, 'self', 'yes', NULL, NULL, 'active'),
(21, 0, NULL, '', '', '', '', '', '', '', '', '', '', '0000-00-00', '', '', '', '', '', '', '', '', '', 0, '', 'mvcc', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2012-10-30 14:45:13', '2012-11-15 19:15:37', NULL, 0, 'self', '', NULL, NULL, 'active'),
(22, 0, NULL, '', '', '', '', '', '', '', '', '', '', '0000-00-00', '', '', '', '', '', '', '', '', '', 0, '', 'mvcc', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2012-10-30 14:45:17', '2012-11-15 19:15:37', NULL, 0, 'self', '', NULL, NULL, 'active'),
(23, 0, NULL, '', '', '', '', '', '', '', '', '', '', '0000-00-00', '', '', '', '', '', '', '', '', '', 0, '', 'mvcc', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2012-10-30 14:50:06', '2012-11-15 19:15:37', NULL, 0, 'self', '', NULL, NULL, 'active'),
(24, 0, NULL, '', '', '', '', '', '', '', '', '', '', '0000-00-00', '', '', '', '', '', '', '', '', '', 0, '', 'mvcc', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2012-10-30 15:08:59', '2012-11-15 19:15:37', NULL, 0, 'self', '', NULL, NULL, 'active'),
(25, 0, NULL, '', '', '', '', '', '', '', '', '', '', '0000-00-00', '', '', '', '', '', '', '', '', '', 0, '', 'mvcc', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2012-10-31 06:09:43', '2012-11-15 19:15:37', NULL, 0, 'self', '', NULL, NULL, 'active'),
(26, 0, NULL, '', '', '', '', '', '', '', '', '', '', '0000-00-00', '', '', '', '', '', '', '', '', '', 0, '', 'mvcc', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2012-10-31 06:14:16', '2012-11-15 19:15:37', NULL, 0, 'self', '', NULL, NULL, 'active'),
(27, 0, NULL, '', '', '', '', '', '', '', '', '', '', '0000-00-00', '', '', '', '', '', '', '', '', '', 0, '', 'mvcc', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2012-10-31 06:16:18', '2012-11-15 19:15:37', NULL, 0, 'self', '', NULL, NULL, 'active'),
(28, 0, NULL, '', '', '', '', '', '', '', '', '', '', '0000-00-00', '', '', '', '', '', '', '', '', '', 0, '', 'mvcc', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2012-10-31 06:18:09', '2012-11-15 19:15:37', NULL, 0, 'self', '', NULL, NULL, 'active'),
(29, 39, 'arn87878', 'dfasdfa', '', '', '', '', '', '', '', '', '', '0000-00-00', '', '', '', '', '', '', '', '', '', 0, '', 'mvcc', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2012-11-01 11:25:27', NULL, NULL, 0, 'self', 'yes', NULL, NULL, 'active'),
(30, 40, 'arn87878', 'dfasdfa', 'jaskldfjkl', 'abc2', 'abc3', 'fsdfasd', 'aaaa', 'dsfa', 'fsda', '', '', '0000-00-00', '', '', '', '', '', '', '', '', '', 0, '', 'mvcc', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2012-11-01 11:29:19', NULL, NULL, 0, 'self', 'yes', NULL, NULL, 'active'),
(31, 41, 'arn87878', 'dfasdfa', 'abc', 'abc2', 'abc3', 'fsdfasd', 'fafda', 'dsfa', 'fsda', '', '', '0000-00-00', '', '', '', '', '', '', '', '', '', 0, '', 'mvcc', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2012-11-01 11:30:23', NULL, NULL, 0, 'self', 'yes', NULL, NULL, 'active'),
(32, 42, 'arn33', 'dfasdfa', 'abc', 'jdfas', 'abc3', 'fsdfasd', 'fafda', 'dsfa', 'ccccc', '', 'indian', '2012-09-09', '', '1212', 'dfasdfasd1', 'dfasfas', 'delhi', 'delhi', 'delhi', 'delhi', 'IN', 232323, 'landmark1', 'mvcc', '', 'general', NULL, 100000, 'aae889', 'rahul', 'kumar', 'vats', '2012-09-12', 'borther', NULL, 'delhi', 'rahul', 'kumar', 'jain', '787878787878', 'CP', '2012-01-12', '', 'yes', 'adkjhf89', '', 'car', 'PG', 2, '2012-11-03 07:59:32', '2012-11-03 08:00:23', NULL, 0, 'self', 'yes', NULL, NULL, 'active'),
(33, 43, 'arn87878', 'dfasdfa', 'abc', 'abc2', 'dsfa', 'fsdfasd', 'fafda', 'fdsfasdfa', 'fsda', '', '', '0000-00-00', '', '', '', '', '', '', '', '', '', 0, '', 'mvcc', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2012-11-03 07:41:03', NULL, NULL, 0, 'self', 'yes', NULL, NULL, 'active'),
(34, 44, '1232', 'dfasdfa', 'Jasdf', 'Pasdf', 'Tiwari', 'Uasdf', 'Usdf', 'Uasd', 'Tadsf', '', '', '0000-00-00', '', '', '', '', '', '', '', '', '', 0, '', 'mvcc', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2012-11-03 07:58:40', NULL, NULL, 0, 'self', 'yes', NULL, NULL, 'active'),
(35, 45, 'arn87878', 'dfasdfa', 'jaskldfjkl', 'jdfas', 'dsfa', 'fsdfasd', 'fafda', 'dsfa', 'fsda', '', '', '0000-00-00', '', '', '', '', '', '', '', '', '', 0, '', 'mvcc', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2012-11-05 05:54:59', NULL, NULL, 0, 'self', 'yes', NULL, NULL, 'active'),
(36, 46, 'arn87878', 'dfasdfa', '', '', '', '', '', '', '', '', '', '0000-00-00', '', '', '', '', '', '', '', '', '', 0, '', 'mvcc', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2012-11-05 06:00:43', NULL, NULL, 0, 'self', 'yes', NULL, NULL, 'active'),
(37, 47, 'arn87878', 'dfasdfa', '', '', '', '', '', '', '', '', '', '0000-00-00', '', '', '', '', '', '', '', '', '', 0, '', 'mvcc', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2012-11-05 06:03:56', NULL, NULL, 0, 'self', 'yes', NULL, NULL, 'active'),
(38, 48, 'af434', 'dfasdfa', '', '', '', '', '', '', '', '', '', '0000-00-00', '', '', '', '', '', '', '', '', '', 0, '', 'mvcc', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2012-11-05 06:04:55', NULL, NULL, 0, 'self', 'yes', NULL, NULL, 'active'),
(39, 49, 'af434', 'dfasdfa', 'dfsdf', 'dfas', 'sdfasda', '', '', '', '', '', '', '0000-00-00', '', '', '', '', '', '', '', '', '', 0, '', 'mvcc', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2012-11-05 12:42:08', NULL, NULL, 0, 'self', 'yes', NULL, NULL, 'active'),
(40, 50, 'af434', 'dfasdfa', 'dfsdf', 'dfas', 'sdfasda', 'sdfsd', 'dfgsdf', 'dfgsdf', '', '', '', '0000-00-00', '', '', '', '', '', '', '', '', '', 0, '', 'mvcc', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2012-11-05 13:01:42', NULL, NULL, 0, 'self', 'yes', NULL, NULL, 'active'),
(41, 51, 'af434', 'dfasdfa', 'dfsdf', 'dfas', 'sdfasda', 'sdfsd', 'dfgsdf', 'dfgsdf', '', '', '', '0000-00-00', '', '', '', '', '', '', '', '', '', 0, '', 'mvcc', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2012-11-05 13:15:33', NULL, NULL, 0, 'self', 'yes', NULL, NULL, 'active'),
(42, 52, 'af434', 'dfasdfa', 'dfsdf', 'dfas', 'sdfasda', 'sdfsd', 'dfgsdf', 'dfgsdf', '', '', 'indian', '2012-12-12', '', '343', '34', 'efqef', 'dfsdfd', 'dsfas', 'fdsa', 'sdfas', 'IN', 434344, 'dsfasd', 'mvcc', '', 'general', NULL, 0, '', '', '', '', '0000-00-00', '', NULL, 'delhi', 'afjskadhkj', 'hh', 'hkj', '45454', 'hfg', '0000-00-00', '', '', 'khk', '', '', '', 1, '2012-11-05 13:19:52', '2012-11-05 13:20:10', NULL, 0, 'self', 'yes', NULL, NULL, 'active'),
(43, 53, 'af434', 'dfasdfa', 'dfsdf', 'dfas', 'sdfasda', 'sdfsd', 'dfgsdf', 'dfgsdf', '', '', 'indian', '2012-12-12', '', '343', '34', 'efqef', 'dfsdfd', 'dsfas', 'fdsa', 'sdfas', 'IN', 434344, 'dsfasd', 'mvcc', '', 'general', NULL, 3434, 'dsfas', 'fdgfgsd', 'fdfgsd', 'dfgsd', '2012-12-12', '', NULL, 'delhi', 'afjskadhkj', 'hh', 'hkj', '45454', 'hfg', '0000-00-00', 'yes', 'yes', 'khk', 'yes', 'gdfgs', 'fdgsd', 1, '2012-11-05 13:29:06', '2012-11-05 13:30:02', NULL, 0, 'self', 'yes', NULL, NULL, 'active'),
(44, 54, 'af434', 'dfasdfa', 'dfsdf', 'dfas', 'sdfasda', 'sdfsd', 'dfgsdf', 'dfgsdf', '', '', 'indian', '2012-12-12', '', '343', '34', 'efqef', 'dfsdfd', 'dsfas', 'fdsa', 'sdfas', 'IN', 434344, 'dsfasd', 'mvcc', '', 'general', NULL, 3434, 'dsfas', 'fdgfgsd', 'fdfgsd', 'dfgsd', '2012-12-12', '', NULL, 'delhi', 'afjskadhkj', 'hh', 'hkj', '45454', 'hfg', '0000-00-00', 'yes', 'yes', 'khk', 'yes', 'gdfgs', 'fdgsd', 1, '2012-11-05 13:36:31', '2012-11-05 13:37:07', NULL, 0, 'self', 'yes', NULL, NULL, 'active'),
(45, 55, 'af434', 'dfasdfa', 'dfsdf', 'dfas', 'sdfasda', 'sdfsd', 'dfgsdf', 'dfgsdf', '', '', '', '0000-00-00', '', '', '', '', '', '', '', '', '', 0, '', 'mvcc', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2012-11-05 13:46:15', NULL, NULL, 0, 'self', 'yes', NULL, NULL, 'active'),
(46, 56, 'af434', 'dfasdfa', 'dfsdf', 'dfas', 'sdfasda', 'sdfsd', 'dfgsdf', 'dfgsdf', 'dfsd', '', 'indian', '2012-12-12', '', '343', '34', 'efqef', 'dfsdfd', 'dsfas', 'fdsa', 'sdfas', 'IN', 434344, 'dsfasd', 'mvcc', '', 'general', NULL, 3434, 'dsfas', 'fdgfgsd', 'fdfgsd', 'dfgsd', '2012-12-12', 'sdfas', NULL, 'delhi', 'afjskadhkj', 'hh', 'hkj', '45454', 'hfg', '0000-00-00', 'yes', 'yes', 'khk', 'yes', 'gdfgs', 'fdgsd', 1, '2012-11-05 14:06:36', '2012-11-05 14:07:29', NULL, 0, 'self', 'yes', NULL, NULL, 'active'),
(47, 57, 'af434', 'dfasdfa', 'dfsdf', 'dfas', 'sdfasda', 'sdfsd', 'dfgsdf', 'dfgsdf', 'dfsd', '', '', '0000-00-00', '', '', '', '', '', '', '', '', '', 0, '', 'mvcc', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2012-11-05 14:33:24', NULL, NULL, 0, 'self', 'yes', NULL, NULL, 'active'),
(48, 58, 'af434', 'dfasdfa', 'dfsdf', 'dfas', 'sdfasda', 'sdfsd', 'dfgsdf', 'dfgsdf', 'dfsd', '', '', '0000-00-00', '', '', '', '', '', '', '', '', '', 0, '', 'mvcc', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2012-11-05 14:49:50', NULL, NULL, 0, 'self', 'yes', NULL, NULL, 'active'),
(49, 59, 'af434', 'dfasdfa', 'dfsdf', 'dfas', 'sdfasda', 'sdfsd', 'dfgsdf', 'dfgsdf', 'dfsd', '', '', '0000-00-00', '', '', '', '', '', '', '', '', '', 0, '', 'mvcc', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2012-11-05 15:19:11', NULL, NULL, 0, 'self', 'yes', NULL, NULL, 'active'),
(50, 60, '67867', 'dfasdfa', 'adfs', 'sadf', 'dsaf', 'sadf', 'asdf', 'asdf', 'adfs', '', '', '0000-00-00', '', '', '', '', '', '', '', '', '', 0, '', 'mvcc', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2012-11-05 15:27:57', NULL, NULL, 0, 'self', 'yes', NULL, NULL, 'active'),
(51, 61, '12345678as', 'dfasdfa', 'Ram', 'Bahadur', 'Singh', 'Singh', 'Preeti', '', 'Singh', '', '', '0000-00-00', '', '', '', '', '', '', '', '', '', 0, '', 'mvcc', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2012-11-05 16:55:21', NULL, NULL, 0, 'self', 'yes', NULL, NULL, 'active'),
(52, 62, '12345678as', 'dfasdfa', 'Ram', 'Bahadur', 'Singh', 'Singh', 'Preeti', '', 'Singh', '', '', '0000-00-00', '', '', '', '', '', '', '', '', '', 0, '', 'mvcc', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2012-11-05 17:07:00', NULL, NULL, 0, 'self', 'yes', NULL, NULL, 'active'),
(53, 63, '12345678as', 'dfasdfa', 'Ram', 'Bahadur', 'Singh', 'Singh', 'Preeti', '', 'Singh', '', '', '0000-00-00', '', '', '', '', '', '', '', '', '', 0, '', 'mvcc', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2012-11-05 17:07:47', NULL, NULL, 0, 'self', 'yes', NULL, NULL, 'active'),
(54, 64, 'af434', 'dfasdfa', 'akash', 'kumar', 'jain', 'pooja', 'shush', 'mita', 'jain', '', 'indian', '2001-08-01', '', '343', 'karol bagh', '', 'delhi', 'delhi', 'delhi', 'delhi', 'IN', 110038, 'near main road', 'mvcc', '', 'general', NULL, 100000, 'ae45782', 'Rahul', 'Kumar', 'Shinde', '2012-12-12', 'brother', NULL, 'delhi', 'Deepak', 'kumar', 'jain', '123456789', 'CP', '2012-07-10', 'yes', 'yes', 'kiuo66999', 'yes', 'Car', 'PG', 2, '2012-11-06 11:26:24', '2012-11-06 11:39:15', NULL, 0, 'self', 'yes', NULL, NULL, 'active'),
(55, 65, 'af434', 'dfasdfa', 'akash', 'kumar', 'jain', 'pooja', 'shush', 'mita', 'jain', '', 'indian', '2012-11-14', '', '343', 'karol bagh', '', 'delhi', '', '', 'delhi', 'IN', 110038, '', 'mvcc', '', 'general', NULL, 0, '', 'Rahul', '', 'Shinde', '2012-11-04', 'brother', NULL, 'delhi', 'Deepak', 'kumar', 'jain', '', '', '2012-03-05', 'yes', 'yes', 'kiuo66999', 'yes', 'two wheeler', 'graduate', 2, '2012-11-06 11:54:30', '2012-11-06 12:06:56', NULL, 0, 'self', 'yes', NULL, NULL, 'active'),
(56, 66, 'af434', 'dfasdfa', 'Rahul', '', 'Roy', 'Bhavna', '', '', '', '', 'indian', '2001-08-01', '', '343', 'karol bagh', '', 'delhi', '', '', 'delhi', 'IN', 110038, 'near main road', 'mvcc', '', 'general', NULL, 0, '', 'Rahul', '', 'Shinde', '2009-06-02', 'brother', NULL, 'delhi', 'Deepak', 'kumar', 'jain', '45454', 'CP', '2012-01-10', 'yes', 'yes', 'kiuo66999', 'yes', 'two wheeler', 'graduate', 2, '2012-11-06 13:13:52', '2012-11-06 13:15:00', NULL, 0, 'self', 'yes', NULL, NULL, 'active'),
(57, 67, 'fgdfgsd', 'dfasdfa', 'akash', 'kumar', 'jain', 'pooja', 'shush', 'mita', 'jain', '', 'indian', '2012-12-12', '', '343', 'New friend colony', '', 'Delhi', 'delhi', '', 'delhi', 'IN', 110038, '', 'mvcc', '', 'general', NULL, 0, '', 'Rahul', 'Kumar', 'Shinde', '2012-05-07', 'brother', NULL, 'delhi', 'Deepak', 'kumar', 'jain', '45454', 'CP', '2012-03-05', 'yes', 'yes', 'kiuo66999', 'yes', 'two wheeler', 'intermediate', 4, '2012-11-06 13:52:17', '2012-11-06 13:53:26', NULL, 0, 'self', 'yes', NULL, NULL, 'active'),
(58, 68, 'arn000123', 'dfasdfa', 'Peter', '', 'John', 'Sonali', '', '', '', '', 'indian', '2012-11-14', '', '343', 'New friend colony', '', 'Delhi', '', '', 'delhi', 'IN', 110038, 'near main road', 'mvcc', '', 'general', NULL, 0, '', 'Rahul', 'Kumar', 'Shinde', '2012-08-07', 'brother', NULL, 'delhi', 'Deepak', 'kumar', 'jain', '45454', 'CP', '2012-11-06', 'yes', 'yes', 'kiuo66999', 'yes', 'two wheeler', 'intermediate', 2, '2012-11-06 14:29:49', '2012-11-06 14:33:58', NULL, 0, 'self', 'yes', NULL, NULL, 'active'),
(59, 69, 'arn000124', 'dfasdfa', 'Anil', '', 'Kumar', 'julie', '', '', '', '', '', '0000-00-00', '', '', '', '', '', '', '', '', '', 0, '', 'mvcc', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2012-11-06 14:49:39', NULL, NULL, 0, 'self', 'yes', NULL, NULL, 'active'),
(60, 70, 'asdfasdf23', 'dfasdfa', 'aa', '', 'aa', 'as', '', '', '', '', '', '0000-00-00', '', '', '', '', '', '', '', '', '', 0, '', 'mvcc', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2012-11-06 14:56:14', NULL, NULL, 0, 'self', 'yes', NULL, NULL, 'active'),
(61, 71, 'arn000124', 'dfasdfa', 'Anil', '', 'Kumar', 'julie', '', '', '', '', '', '0000-00-00', '', '', '', '', '', '', '', '', '', 0, '', 'mvcc', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2012-11-06 15:18:25', NULL, NULL, 0, 'self', 'yes', NULL, NULL, 'active'),
(62, 72, 'asdfasdf33', 'dfasdfa', 'aa', '', 'aaa', 'asdf', '', '', '', '', '', '0000-00-00', '', '', '', '', '', '', '', '', '', 0, '', 'mvcc', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2012-11-06 15:28:54', NULL, NULL, 0, 'self', 'yes', NULL, NULL, 'active'),
(63, 73, 'asdfasdf23', 'dfasdfa', 'adsf', '', 'adsf', 'dsf', '', '', '', '', '', '0000-00-00', '', '', '', '', '', '', '', '', '', 0, '', 'mvcc', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2012-11-06 15:42:27', NULL, NULL, 0, 'self', 'yes', NULL, NULL, 'active'),
(64, 74, 'af434', 'dfasdfa', 'akash', '', 'jain', 'pooja', '', '', '', '', 'indian', '2011-12-06', '', '343', 'karol bagh', '', 'Anjangaon', 'delhi', 'delhi', 'Maharashtra', 'IN', 110038, '', 'mvcc', '', 'general', NULL, 343443, '', 'Rahul', '', 'Shinde', '2012-09-03', 'brother', NULL, 'delhi', 'Deepak', 'kumar', 'jain', '45454', 'CP', '2012-09-03', 'yes', 'yes', 'kiuo66999', 'yes', 'two wheeler', 'intermediate', 1, '2012-11-06 15:45:47', '2012-11-06 15:52:51', NULL, 0, 'self', 'yes', NULL, NULL, 'active'),
(65, 75, 'af434', 'dfasdfa', 'akash', '', 'Kumar', 'pooja', '', '', '', '', 'indian', '2011-09-06', '', '343', 'karol bagh', '', 'Anjangaon', '', '', 'Maharashtra', 'IN', 223365, '', 'mvcc', '', 'general', NULL, 0, '', 'Rahul', '', 'Shinde', '2012-09-03', 'brother', NULL, 'delhi', 'Deepak', 'kumar', 'jain', '45454', 'CP', '2012-05-07', 'yes', 'yes', 'kiuo66999', 'yes', 'two wheeler', 'intermediate', 1, '2012-11-06 16:12:25', '2012-11-06 16:13:26', NULL, 0, 'self', 'yes', NULL, NULL, 'active'),
(66, 76, 'af434', 'dfasdfa', 'akash', '', 'jain', 'pooja', 'shush', 'mita', 'jain', '', '', '0000-00-00', '', '', '', '', '', '', '', '', '', 0, '', 'mvcc', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2012-11-07 18:45:41', NULL, NULL, 0, 'self', 'yes', NULL, NULL, 'active'),
(67, 77, 'af434', 'dfasdfa', 'akash', '', 'jain', 'pooja', 'shush', 'mita', 'jain', '', '', '0000-00-00', '', '', '', '', '', '', '', '', '', 0, '', 'mvcc', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2012-11-07 18:46:36', NULL, NULL, 0, 'self', 'yes', NULL, NULL, 'active'),
(68, 78, 'arn000128', 'dfasdfa', 'akash', '', 'jain', 'pooja', 'shush', '', 'jain', '', '', '0000-00-00', '', '', '', '', '', '', '', '', '', 0, '', 'mvcc', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2012-11-07 18:47:16', NULL, NULL, 0, 'self', 'yes', NULL, NULL, 'active'),
(69, 79, 'arn1234', 'dfasdfa', 'akash', '', 'jain', 'pooja', 'shush', '', 'jain', '', 'indian', '2012-11-26', 'male', '343', 'New friend colony', '', '', 'mumbai', 'mumbai', 'Maharashtra', 'IN', 223365, '', 'mvcc', '', 'general', NULL, 0, '', 'Rahul', '', 'Shinde', '2012-09-03', 'brother', NULL, 'mumbai', 'Deepak', 'kumar', 'jain', '45454', 'CP', '2012-03-05', 'yes', 'yes', 'kiuo66999', 'yes', 'two wheeler', 'graduate', 2, '2012-11-07 18:50:17', '2012-11-07 19:10:43', NULL, 0, 'self', 'yes', NULL, NULL, 'active'),
(70, 80, 'arn000128', 'dfasdfa', 'permender', '', 'kumar', 'pamela', 'nonie', '', 'sharma', '', '', '0000-00-00', '', '', '', '', '', '', '', '', '', 0, '', 'mvcc', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2012-11-07 19:00:40', NULL, NULL, 0, 'self', 'yes', NULL, NULL, 'active'),
(71, 81, 'arn000129', 'dfasdfa', 'Peter', '', 'John', 'pooja', '', '', '', '', 'indian', '2011-12-05', 'male', '5874', 'karol bagh', '', 'Ashta', 'mumbai', 'mumbai', 'Maharashtra', 'IN', 223365, '', 'mvcc', '', 'sc', NULL, 100000, 'ae45782', 'Rahul', '', 'Shinde', '2012-08-06', 'brother', NULL, 'delhi', 'Deepak', 'kumar', 'jain', '', '', '2012-10-08', 'yes', 'yes', 'kiuo66999', 'yes', 'car', 'post graduate', 3, '2012-11-07 19:20:52', '2012-11-07 19:22:58', NULL, 0, 'self', 'yes', NULL, NULL, 'active'),
(72, 82, 'arn000235', '9878563336', 'sushil', '', 'kumar', 'sonali', 'poo', '', 'jha', '', '', '0000-00-00', 'male', '', '', '', '', '', '', '', '', 0, '', 'mvcc', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2012-11-07 19:59:22', NULL, NULL, 0, 'self', 'yes', NULL, NULL, 'active'),
(73, 83, 'arn12345', '9874561236', 'Rahul', '', 'jain', 'priyanka', 'shush', '', 'mita', '', 'indian', '2011-07-04', 'male', '5874', 'karol bagh', '', 'Akola', 'mumbai', 'mumbai', 'Maharashtra', 'IN', 223365, '', 'mvcc', '', 'general', NULL, 80000, 'ae45782', 'Rahul', '', 'Shinde', '2012-10-01', 'brother', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2012-11-08 10:53:45', '2012-11-08 13:10:31', NULL, 0, 'self', 'yes', NULL, NULL, 'active'),
(74, 84, 'af434', '9878563336', 'Sushil', '', 'Jha', 'Priyanka', '', '', '', '', 'indian', '2012-11-08', 'male', '5874', 'New friend colony', '', '', 'mumbai', 'mumbai', 'Maharashtra', 'IN', 223365, '', 'mvcc', '', 'general', NULL, 150000, 'ae45782', 'Rahul', '', 'Shinde', '2012-07-02', 'brother', NULL, 'delhi', 'Deepak', '', 'jain', '45454', 'CP', '2012-10-01', 'yes', 'no', 'kiuo66999', 'yes', 'car', 'graduate', 2, '2012-11-08 10:58:03', '2012-11-08 11:00:51', NULL, 0, 'self', 'yes', NULL, NULL, 'active'),
(75, 85, 'ARNNUMBER', '', 'Ram', 'Bahadur', 'Singh', 'Singh', '', '', '', '', 'indian', '2012-11-30', 'male', 'dfgdg', 'sdfgdfg', 'dfgdfg', 'Anjangaon', 'dfgdgf', 'sdfgsdfg', 'Maharashtra', 'IN', 0, 'sdfasdf', 'mvcc', '', 'general', NULL, 0, 'zxcvasdf', 'aa', '', 'bb', '2012-11-30', 'fhfghfg', NULL, 'weewerwe', 'werwer', '', 'wer2rr', '', '', '2012-11-04', 'yes', 'yes', '122354568709', 'yes', 'car', 'graduate', 5, '2012-11-08 12:36:20', '2012-11-08 12:47:26', NULL, 0, 'self', 'yes', NULL, NULL, 'active'),
(76, 86, 'arn9', '9878563356', 'akash', '', 'jain', 'pooja', 'shush', '', '', '', 'indian', '2012-03-06', 'male', '5874', 'karol bagh', '', 'Akot', 'mumbai', 'mumbai', 'Maharashtra', 'IN', 223365, '', 'mvcc', '', 'general', NULL, 100000, 'ae45782', 'Rahul', '', 'Shinde', '2012-07-03', 'brother', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2012-11-08 13:14:22', '2012-11-08 13:27:43', NULL, 0, 'self', 'yes', NULL, NULL, 'active'),
(78, 88, 'arn98745', '9878568745', 'akash', '', 'jain', 'pooja', 'shush', '', 'jain', '', '', '0000-00-00', 'male', '', '', '', '', '', '', '', '', 0, '', 'mvcc', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2012-11-08 14:14:00', NULL, NULL, 0, 'self', 'yes', NULL, NULL, 'active'),
(79, 89, 'arn000235', '9878563336', 'Anil', '', 'jain', 'sonali', '', '', '', '', 'indian', '2011-12-01', 'male', '12345', 'karol bagh', '', 'Ahmedpur', 'mumbai', 'mumbai', 'Maharashtra', 'IN', 223365, '', 'mvcc', '', 'general', NULL, 100000, 'ae45782', 'Rahul', '', 'Shinde', '2012-10-02', 'mother', NULL, 'mumbai', 'Deepak', '', 'jain', '45454', 'CP', '2012-10-01', 'yes', 'yes', 'kiuo66999', 'yes', 'car', 'graduate', 2, '2012-11-08 14:23:25', '2012-11-08 15:01:44', NULL, 0, 'self', 'yes', NULL, NULL, 'active'),
(80, 90, 'arn8777', '9878563336', 'GS', '', 'Saini', 'Sonali', 'Ankur', '', 'Saini', '', '', '0000-00-00', 'male', '', '', '', '', '', '', '', '', 0, '', 'mvcc', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2012-11-08 15:51:20', NULL, NULL, 0, 'self', 'yes', NULL, NULL, 'active'),
(81, 91, 'arn1234522', '9878563336', 'Lalit', '', 'Jain', 'sushil', 'shush', '', 'jain', '', '', '0000-00-00', 'male', '', '', '', '', '', '', '', '', 0, '', 'mvcc', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2012-11-08 16:22:10', NULL, NULL, 0, 'self', 'yes', NULL, NULL, 'active'),
(82, 92, 'arn0036987', '9878563336', 'jitender', '', 'varish', 'pooja', 'shush', '', 'jain', '', 'indian', '2012-06-05', 'male', '5874', 'karol bagh', '', 'Ausa', 'mumbai', 'mumbai', 'Maharashtra', 'IN', 223365, '', 'mvcc', '', 'general', NULL, 100000, 'ae45782', 'Rahul', '', 'Shinde', '2012-09-04', 'father', NULL, 'delhi', 'Deepak', '', 'jain', '45454', 'CP', '2012-10-01', 'yes', 'yes', 'kiuo66999', 'yes', 'car', 'graduate', 2, '2012-11-08 16:36:53', '2012-11-08 16:38:09', NULL, 0, 'self', 'yes', NULL, NULL, 'active'),
(83, 93, 'arn006128', '9874561236', 'akash', '', 'jain', 'pooja', 'shush', '', 'jain', '', '', '0000-00-00', 'male', '', '', '', '', '', '', '', '', 0, '', 'mvcc', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2012-11-08 16:40:34', NULL, NULL, 0, 'self', 'yes', NULL, NULL, 'active'),
(85, 95, 'arn000124', '9878563336', 'Sumit', '', 'Kumar', 'Geetu', '', '', '', '', 'indian', '2012-07-11', 'male', '235', 'karol bagh', '', 'Gondiya', 'mumbai', 'mumbai', 'Maharashtra', 'IN', 223365, '', 'mvci', '', 'general', NULL, 100000, 'ae45782', 'Rahul', '', 'Shinde', '2012-08-07', 'brother', NULL, 'mumbai', 'Deepak', 'kumar', 'jain', '45454', 'CP', '2012-08-22', 'yes', 'yes', 'kiuo66999', 'yes', 'car', 'post graduate', 2, '2012-11-08 17:04:32', '2012-11-08 17:13:29', NULL, 0, 'self', 'yes', NULL, NULL, 'active'),
(86, 96, 'arn123458', '9874561236', 'Sunil', '', 'Jain', 'Amita', 'shush', '', 'mita', '', 'indian', '2012-04-03', 'male', '5874', 'New friend colony', '', 'Gondiya', 'mumbai', 'mumbai', 'Maharashtra', 'IN', 223365, '', 'mvci', '', 'obc', NULL, 100000, 'ae45782', 'Rahul', '', 'Shinde', '2012-08-15', 'sister', NULL, 'mumbai', 'Deepak', '', 'jain', '45454', 'CP', '2012-10-15', 'yes', 'yes', 'kiuo66999', 'yes', 'car', 'graduate', 2, '2012-11-08 17:58:40', '2012-11-08 17:59:43', NULL, 0, 'self', 'yes', NULL, NULL, 'active'),
(87, 97, 'arn9999', '9978563336', 'Jai', '', 'Kishan', 'Urmila', 'Nonie', '', 'Seth', '', '', '0000-00-00', 'male', '', '', '', '', '', '', '', '', 0, '', 'mvcc', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2012-11-08 19:32:18', NULL, NULL, 0, 'self', 'yes', NULL, NULL, 'active'),
(88, 98, 'arn994', '9978563336', 'Jai', '', 'Kishan', 'Urmila', 'Nonie', '', 'Seth', '', 'indian', '2012-06-12', 'male', '9898', 'New friend colony', '', 'Alandi', 'mumbai', 'mumbai', 'Maharashtra', 'IN', 223365, '', 'mvci', '', 'sc', NULL, 150000, 'dsf4r44', 'Rahul', '', 'Seth', '2012-05-16', 'mother', NULL, 'mumbai', 'Deepak', '', 'jain', '45454', 'CP', '2012-11-13', 'yes', 'yes', 'kiuo66999', 'yes', 'car', 'graduate', 2, '2012-11-08 20:04:34', '2012-11-08 20:04:26', NULL, 0, 'self', 'yes', NULL, NULL, 'active'),
(89, 99, 'arn0012256', '9978563336', 'Vinod', '', 'Anand', 'Julie', 'Janith', '', 'Jackson', '', 'indian', '2012-05-08', 'male', '5874', 'karol bagh', '', 'Latur', 'mumbai', 'mumbai', 'Maharashtra', 'IN', 223365, 'near main road', 'mvci', '', 'obc', NULL, 150000, 'ae45782', 'Rahul', '', 'Shinde', '2012-07-11', 'brother', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2012-11-09 10:56:19', '2012-11-09 10:57:44', NULL, 0, 'self', 'yes', NULL, NULL, 'active'),
(90, 100, 'arn99', '9878563336', 'akash', '', 'Kishan', 'Urmila', 'Nonie', '', 'Seth', '', 'indian', '2012-08-07', 'male', '5874', 'karol bagh', '', 'Arvi', 'mumbai', 'mumbai', 'Maharashtra', 'IN', 223365, 'near main road', 'mvci', '', 'obc', NULL, 100000, 'dsf4r44', 'Rahul', '', 'Shinde', '2012-07-17', 'father', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2012-11-09 11:00:47', '2012-11-09 11:09:07', NULL, 0, 'self', 'yes', NULL, NULL, 'active'),
(91, 101, 'arn99', '9878563336', 'Ashok', '', 'Kumar', 'pooja', 'Nonie', '', 'mita', '', 'indian', '2012-02-06', 'male', '12345', 'New friend colony', '', 'Ajra', 'mumbai', 'mumbai', 'Maharashtra', 'IN', 223365, '', 'mvcc', '', 'obc', NULL, 100000, 'ae45782', 'Rahul', '', 'Shinde', '2012-09-18', 'sister', NULL, 'mumbai', 'Deepak', '', 'jain', '45454', 'CP', '2012-10-08', 'yes', 'yes', 'kiuo66999', 'yes', 'car', 'graduate', 2, '2012-11-09 11:15:10', '2012-11-09 11:16:14', NULL, 0, 'self', 'yes', NULL, NULL, 'active'),
(95, 105, 'arn100009', '9878563336', 'akash', '', 'Kumar', 'Urmila', '', '', '', '', '', '0000-00-00', 'male', '', '', '', '', '', '', '', '', 0, '', 'mvcc', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2012-11-09 12:28:27', NULL, NULL, 0, 'self', 'yes', NULL, NULL, 'active'),
(96, 106, 'arn000235', '9874561236', 'Ashu', '', 'Vats', 'Sonali', '', '', '', 'rented', 'indian', '2012-06-05', 'male', '5874', 'New friend colony', '', 'Dombivli', 'mumbai', 'mumbai', 'Maharashtra', 'IN', 223365, '', 'mvci', '', 'sc', NULL, 100000, 'dsf4r44', 'Rahul', '', 'Shinde', '2012-10-01', 'mother', NULL, 'delhi', 'Deepak', '', 'jain', '45454', 'CP', '2012-07-10', 'yes', 'yes', 'kiuo66999', 'yes', 'car', 'graduate', 2, '2012-11-09 16:33:08', '2012-11-09 16:33:58', NULL, 0, 'self', 'yes', NULL, NULL, 'active'),
(97, 107, 'arn000235', '9878563336', 'akash', '', 'jain', 'Urmila', 'Nonie', '', 'mita', '', '', '0000-00-00', 'male', '', '', '', '', '', '', '', '', 0, '', 'mvci', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2012-11-09 16:45:30', NULL, NULL, 0, 'self', 'yes', NULL, NULL, 'active'),
(98, 108, 'arn000235', '9978563336', 'akash', '', 'Kishan', 'Urmila', '', '', '', '', 'indian', '2012-10-02', 'male', '12345', 'karol bagh', '', 'Ahmedpur', 'mumbai', 'mumbai', 'Maharashtra', 'IN', 223365, '', 'mvci', '', 'general', NULL, 100000, 'ae45782', 'Rahul', '', 'Shinde', '2012-10-09', 'mother', NULL, 'mumbai', 'Deepak', '', 'jain', '45454', 'CP', '2012-03-05', 'yes', 'yes', 'kiuo66999', 'yes', 'car', 'intermediate', 2, '2012-11-09 16:56:30', '2012-11-09 16:55:36', NULL, 0, 'self', 'yes', NULL, NULL, 'active'),
(99, 109, 'arn001', '9789898969', 'akash', '', 'Kumar', 'pooja', 'Nonie', '', 'Seth', '', 'indian', '2010-01-06', 'male', '5874', 'New Friend colony', '', 'Akot', 'mumbai', 'mumbai', 'Maharashtra', 'IN', 223365, '', 'mvci', '', 'obc', NULL, 100000, 'ae45782', 'Rahul', '', 'Shinde', '2012-04-02', 'sister', NULL, 'mumbai', 'Neeraj', '', 'Jain', '45454', 'CP', '2012-08-06', 'yes', 'yes', 'kiuo66999', 'yes', 'car', 'graduate', 2, '2012-11-09 17:50:58', '2012-11-09 17:53:17', NULL, 0, 'self', 'yes', NULL, NULL, 'active'),
(100, 110, 'arn9898', '9978563336', 'Sandip', '', 'Seth', 'Sonali', '', '', '', '', 'indian', '2012-08-08', 'male', '5874', 'karol bagh', '', 'Achalpur', 'mumbai', 'mumbai', 'Maharashtra', 'IN', 223365, '', 'mvci', '', 'obc', NULL, 100000, 'ae45782', 'Rahul', '', 'Shinde', '2012-09-05', 'father', NULL, 'delhi', 'Deepak', 'kumar', 'jain', '45454', 'CP', '2012-08-08', 'yes', 'yes', 'kiuo66999', 'yes', 'two wheeler', 'graduate', 2, '2012-11-09 17:58:31', '2012-11-09 17:59:11', NULL, 0, 'self', 'yes', NULL, NULL, 'active'),
(101, 111, 'arn001', '9810123445', 'lALIT', '', 'Jain', 'Sumita', '', '', '', '', 'indian', '2012-02-07', 'male', '4545', '12344, karol bagh', '', 'Akot', '', '', 'Maharashtra', 'IN', 223443, '', 'mvcc', '', 'obc', NULL, 10000, 'ae4545', 'smita', '', 'jain', '2012-09-04', 'brother', NULL, 'mumbai', 'vikram', '', 'kumar', '1234567899877', 'mumbai', '2012-10-02', 'yes', 'yes', 'uid87767', 'yes', 'car', 'graduate', 2, '2012-11-09 18:53:29', '2012-11-09 18:56:43', NULL, 0, 'self', 'yes', NULL, NULL, 'active'),
(102, 112, 'arn000235', '9978563336', 'Ashu', '', 'Kishan', 'Urmila', '', '', '', '', 'indian', '2012-10-08', 'male', '12345', 'karol bagh', '', 'Pulgaon', 'mumbai', 'mumbai', 'Maharashtra', 'IN', 223365, '', 'mvcc', '', 'obc', NULL, 100000, 'ae45782', 'Rahul', '', 'Shinde', '2012-10-10', 'mother', NULL, 'delhi', 'Deepak', '', 'jain', '45454', 'CP', '2012-10-02', 'yes', 'yes', 'kiuo66999', 'yes', 'car', 'intermediate', 2, '2012-11-09 19:15:58', '2012-11-09 19:17:58', NULL, 0, 'self', 'yes', NULL, NULL, 'active'),
(103, 113, 'arn000235', '9978563336', 'akash', '', 'Kishan', 'Urmila', 'Nonie', '', 'Seth', '', 'indian', '2012-04-09', 'male', '12345', 'karol bagh', '', 'Ashta', 'mumbai', 'mumbai', 'Maharashtra', 'IN', 223365, '', 'mvcc', '', 'obc', NULL, 100000, 'ae45782', 'Rahul', '', 'Shinde', '2012-10-07', 'father', NULL, 'delhi', 'Deepak', '', 'jain', '45454', 'CP', '2012-10-09', 'yes', 'yes', 'kiuo66999', 'yes', 'car', 'graduate', 2, '2012-11-12 11:28:05', '2012-11-12 11:29:30', NULL, 0, 'self', 'yes', NULL, NULL, 'active'),
(104, 1, 'arn000235', '', '', '', '', '', '', '', '', '', '', '2012-06-05', 'male', '', '', '', '', '', '', '', '', 0, '', 'mvcc', '2we2e', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2012-11-14 16:51:19', NULL, NULL, 0, 'self', 'yes', NULL, NULL, 'active'),
(105, 1, 'arn0000122', '', '', '', '', '', '', '', '', '', '', '2012-07-10', 'male', '', '', '', '', '', '', '', '', 0, '', 'mvcc', '2we2e', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2012-11-14 17:20:28', NULL, NULL, 0, 'self', 'yes', NULL, NULL, 'active'),
(106, 119, 'af434', '', '', '', '', '', '', '', '', '', '', '2012-05-15', 'male', '', '', '', '', '', '', '', '', 0, '', 'mvcc', 'sdfa3r3', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2012-11-14 17:44:03', NULL, NULL, 0, 'self', 'yes', NULL, NULL, 'active'),
(107, 120, 'arn997712', '', '', '', '', '', '', '', '', '', '', '2012-03-06', 'male', '', '', '', '', '', '', '', '', 0, '', 'mvcc', 'dvc99889', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2012-11-14 18:32:00', NULL, NULL, 0, 'self', 'yes', NULL, NULL, 'active'),
(108, 121, 'arn0002351', '', '', '', '', '', '', '', '', 'owned', '', '2012-10-01', 'male', '', '', '', '', '', '', '', '', 0, '', 'mvcc', '2we2e', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2012-11-14 20:32:20', NULL, NULL, 43, 'agent', 'yes', NULL, NULL, 'active'),
(109, 122, 'ARN00222', '', '', '', '', '', '', '', '', 'owned', '', '2012-07-16', 'male', '', '', '', '', '', '', '', '', 0, '', 'mvcc', 'dvc998891', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2012-11-15 13:08:42', NULL, NULL, 43, 'agent', 'yes', NULL, NULL, 'inactive'),
(110, 123, 'arn99123', '', '', '', '', '', '', '', '', 'owned', '', '2012-10-10', 'male', '', '', '', '', '', '', '', '', 0, '', 'mvci', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2012-11-15 13:17:35', NULL, NULL, 43, 'agent', 'yes', NULL, NULL, 'inactive'),
(111, 124, 'arn99678', '', '', '', '', '', '', '', '', 'owned', '', '2012-04-10', 'male', '', '', '', '', '', '', '', '', 0, '', 'mvcc', 'dvc99889', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2012-11-15 13:41:20', NULL, NULL, 43, 'agent', 'yes', '', '', 'inactive'),
(112, 125, 'arn000111', '', '', '', '', '', '', '', '', 'owned', '', '2012-05-08', 'male', '', '3434/3434', '', 'Ambivali Tarf Wankhal', '', '', 'Maharashtra', 'IN', 223366, '', 'mvcc', 'dvc99889', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'yes', 'two wheeler', 'intermediate', 3, '2012-11-16 11:48:26', '2012-11-16 13:04:30', NULL, 43, 'agent', 'yes', '0', '0', 'inactive'),
(113, 126, 'af4343', '', '', '', '', '', '', '', '', 'owned', '', '2012-08-06', 'male', '', '', '', '', '', '', '', '', 0, '', 'mvcc', 'dvc99889', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2012-11-16 11:56:45', NULL, NULL, 43, 'agent', 'yes', NULL, NULL, 'inactive'),
(114, 127, 'ar3343', '', '', '', '', '', '', '', '', 'owned', '', '2012-07-10', 'male', '', '3434/3434', '', 'Ahmedpur', '', '', 'Maharashtra', 'IN', 223366, '', 'mvcc', 'dev45454', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'yes', 'two wheeler', 'post graduate', 3, '2012-11-16 13:17:08', '2012-11-16 13:17:33', NULL, 43, 'agent', 'yes', NULL, NULL, 'inactive'),
(115, 128, 'arn0002', '', '', '', '', 'Sunita', '', '', '', 'owned', '', '2011-08-02', 'male', '', '1254/45', 'Karol bagh', 'Ahmedpur', '', '', 'Maharashtra', 'IN', 223365, '', 'mvcc', 'dvc-78787', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'yes', 'two wheeler', 'post graduate', 2, '2012-11-17 12:01:24', '2012-11-17 12:01:30', NULL, 43, 'agent', 'yes', '1', '1', 'inactive'),
(116, 129, 'arn0002', '', '', '', '', 'Sunita', '', '', '', 'owned', '', '2012-05-14', 'male', '', '1254/45', '', 'Alandi', '', '', 'Maharashtra', 'IN', 223365, '', 'mvcc', 'dvc-9887', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'yes', 'two wheeler', 'post graduate', 2, '2012-11-17 12:20:42', '2012-11-17 12:24:20', NULL, 43, 'agent', 'yes', '1', '1', 'inactive'),
(117, 130, 'arn0002', '', '', '', '', 'Sunita', '', '', '', 'owned', '', '2012-08-01', 'male', '', '1254/45', 'Karol bagh', 'Akot', '', '', 'Maharashtra', 'IN', 223365, '', 'mvcc', 'dvc-78787', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'yes', 'two wheeler', 'post graduate', 1, '2012-11-17 12:39:59', '2012-11-17 12:40:57', NULL, 43, 'agent', 'yes', '1', '1', 'inactive'),
(118, 131, 'arn00099', '01154454545', '', '', '', 'Sunita', '', '', '', 'owned', '', '2012-07-03', 'male', '', '878/343', 'Shakti Nagar', 'Ambejogai', '', '', 'Maharashtra', 'IN', 223658, '', 'mvcc', 'dvc98989', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'yes', 'car', 'graduate', 2, '2012-11-19 12:32:46', '2012-11-19 12:34:11', NULL, 43, 'agent', 'yes', '1', '1', 'inactive'),
(119, 132, 'arn98988', '8989898989', '', '', '', 'Sunita', '', '', '', 'owned', '', '2012-06-04', 'male', '', '4545/454', '', 'Ahmedpur', '', '', 'Maharashtra', 'IN', 223658, '', 'mvcc', 'dvc56565', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'yes', 'car', 'graduate', 2, '2012-11-19 12:44:58', '2012-11-19 12:45:33', NULL, 43, 'agent', 'yes', '1', '1', 'inactive'),
(120, 133, 'arn98988', '8989898989', '', '', '', 'Sunita', '', '', '', 'owned', '', '2012-06-05', 'male', '', '4545/454', '', 'Ahmedpur', '', '', 'Maharashtra', 'IN', 223658, '', 'mvcc', 'dvc56565', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'yes', 'two wheeler', 'intermediate', 2, '2012-11-19 13:07:20', '2012-11-19 13:07:43', NULL, 43, 'agent', 'yes', '1', '1', 'inactive'),
(121, 134, 'arn98988', '8989898989', '', '', '', 'Sunita', '', '', '', 'owned', '', '2012-07-09', 'female', '', '4545/454', 'karol bagh', 'Akot', '', '', 'Maharashtra', 'IN', 223658, '', 'mvcc', 'dvc56565', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'yes', 'car', 'intermediate', 2, '2012-11-19 13:33:06', '2012-11-19 13:33:35', NULL, 43, 'agent', 'yes', '1', '1', 'inactive'),
(122, 135, 'arn98988', '8989898989', '', '', '', 'Sunita', '', '', '', 'owned', '', '2012-04-04', 'male', '', '4545/454', 'karol bagh', 'Akot', '', '', 'Maharashtra', 'IN', 223658, '', 'mvcc', 'dvc5896', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'yes', 'car', 'graduate', 2, '2012-11-19 13:37:23', '2012-11-19 13:37:47', NULL, 43, 'agent', 'yes', '1', '1', 'inactive');
INSERT INTO `t_cardholder_details` (`id`, `cardholder_id`, `arn`, `alternate_contact_number`, `father_first_name`, `father_middle_name`, `father_last_name`, `mother_maiden_name`, `spouse_first_name`, `spouse_middle_name`, `spouse_last_name`, `res_type`, `nationality`, `date_of_birth`, `gender`, `flat_number`, `address_line1`, `address_line2`, `city`, `taluka`, `district`, `state`, `country`, `pincode`, `landmark`, `customer_mvc_type`, `device_id`, `caste_category`, `profession`, `annual_income`, `pan_number`, `nominee_first_name`, `nominee_middle_name`, `nominee_last_name`, `date_of_birth_nominee`, `relationship_with_applicant`, `declaration`, `place`, `introducer_first_name`, `introducer_middle_name`, `introducer_last_name`, `introducer_account_number`, `introducer_branch`, `introducer_know_since`, `id_proof_attached`, `address_proof_attached`, `uid_number`, `already_bank_account`, `vehicle_type`, `educational_qualifications`, `family_members`, `date_created`, `date_modified`, `date_activated`, `registration_id`, `registration_type`, `shmart_rewards`, `products_acknowledgement`, `rewards_acknowledgement`, `status`) VALUES
(123, 136, 'arn98988', '8989898989', '', '', '', 'Sunita', '', '', '', 'owned', '', '2012-07-10', 'male', '', '4545/454', 'karol bagh', 'Ahmednagar', '', '', 'Maharashtra', 'IN', 223658, '', 'mvcc', 'dvc56565', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'yes', 'two wheeler', 'intermediate', 1, '2012-11-19 14:22:20', '2012-11-19 14:22:55', NULL, 43, 'agent', 'no', '1', '1', 'inactive'),
(124, 137, 'ARN1234567', '', '', '', '', 'Singh', '', '', '', 'owned', '', '2012-11-04', 'male', '', 'My Address', 'My Address line 2', 'Mumbai', '', '', 'Maharashtra', 'IN', 112012, '', 'mvcc', '123456789', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'yes', 'two wheeler', 'intermediate', 4, '2012-11-19 14:53:04', '2012-11-19 14:53:54', NULL, 43, 'agent', 'yes', '1', '1', 'inactive'),
(125, 138, 'arn98988', '', '', '', '', '', '', '', '', 'owned', '', '2012-05-07', 'male', '', '', '', '', '', '', '', '', 0, '', 'mvcc', 'dvc56565', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2012-11-19 15:07:17', NULL, NULL, 43, 'agent', 'yes', NULL, NULL, 'inactive'),
(126, 139, 'arn98989', '8989898989', '', '', '', 'Sunita', '', '', '', 'owned', '', '2011-10-04', 'male', '', '4545/454', 'karol bagh', 'Ahmednagar', '', '', 'Maharashtra', 'IN', 223658, '', 'mvcc', 'dvccc4545', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'yes', 'two wheeler', 'graduate', 1, '2012-11-19 15:43:21', '2012-11-19 15:45:12', NULL, 43, 'agent', 'yes', '1', '0', 'inactive'),
(127, 140, 'arn98988', '8989898989', '', '', '', 'Sunita', '', '', '', 'owned', '', '2012-01-11', 'male', '', '4545/454', 'karol bagh', 'Ajra', '', '', 'Maharashtra', 'IN', 223658, '', 'mvcc', 'dvc56565', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'yes', 'two wheeler', 'graduate', 2, '2012-11-19 15:54:41', '2012-11-19 15:55:13', NULL, 43, 'agent', 'yes', '1', '1', 'inactive'),
(128, 141, 'arn98988', '8989898989', '', '', '', 'Sunita', '', '', '', 'owned', '', '2012-04-10', 'male', '', '4545/454', 'karol bagh', 'Ajra', '', '', 'Maharashtra', 'IN', 223658, '', 'mvcc', 'dvc56565', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'yes', 'car', 'post graduate', 3, '2012-11-19 16:30:11', '2012-11-19 16:30:41', NULL, 43, 'agent', 'yes', '1', '1', 'inactive'),
(129, 142, 'AR879873', '02238794783', '', '', '', '', '', '', '', 'owned', '', '1979-09-09', 'male', '', 'Mumbai', '', '', '', '', 'Maharashtra', 'IN', 200000, '', 'mvcc', '3454345', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'yes', 'car', 'intermediate', 2, '2012-11-19 18:58:29', '2012-11-19 19:01:02', NULL, 83, 'agent', 'yes', '1', '1', 'inactive'),
(130, 143, 'arn98988', '8989898989', '', '', '', 'Sunita', '', '', '', 'owned', '', '2012-04-03', 'male', '', '4545/454', 'karol bagh', 'Akot', '', '', 'Maharashtra', 'IN', 223658, '', 'mvcc', 'dvc56565', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'yes', 'car', 'post graduate', 2, '2012-11-20 14:15:34', '2012-11-20 14:17:20', NULL, 43, 'agent', 'yes', '1', '1', 'inactive'),
(131, 144, 'arn98988', '8989898989', '', '', '', 'Sunita', '', '', '', 'owned', '', '2012-04-09', 'male', '', '4545/454', 'karol bagh', 'Akkalkot', '', '', 'Maharashtra', 'IN', 223658, '', 'mvcc', 'dvc56565', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'yes', 'car', 'graduate', 1, '2012-11-20 14:39:20', '2012-11-20 15:37:40', NULL, 43, 'agent', 'yes', NULL, NULL, 'inactive'),
(132, 145, 'arn91235', '8989898989', '', '', '', 'Sunita', '', '', '', 'owned', '', '2012-05-01', 'male', '', '4545/454', 'karol bagh', 'Akkalkot', '', '', 'Maharashtra', 'IN', 223658, '', 'mvcc', 'dvc5896', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'yes', 'two wheeler', 'graduate', 1, '2012-11-20 15:52:52', '2012-11-20 15:53:59', NULL, 43, 'agent', 'no', NULL, NULL, 'inactive'),
(133, 146, 'arn563', '8989898989', '', '', '', 'Sunita', '', '', '', 'owned', '', '2012-04-02', 'male', '', '4545/454', 'karol bagh', 'Akot', '', '', 'Maharashtra', 'IN', 223655, '', 'mvcc', 'dvc56565', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'yes', 'car', 'graduate', 3, '2012-11-20 16:10:40', '2012-11-20 16:51:01', NULL, 43, 'agent', 'yes', NULL, NULL, 'inactive'),
(134, 147, 'arn5633', '8989898989', '', '', '', 'Sunita', '', '', '', 'owned', '', '2011-10-04', 'male', '', '4545/454', 'karol bagh', 'Akkalkot', '', '', 'Maharashtra', 'IN', 223658, '', 'mvcc', 'dvc56565', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'yes', 'two wheeler', 'graduate', 2, '2012-11-20 16:53:01', '2012-11-20 17:01:02', NULL, 43, 'agent', 'yes', NULL, NULL, 'inactive'),
(135, 148, 'arn989893', '', '', '', '', '', '', '', '', 'owned', '', '2012-05-08', 'male', '', '', '', '', '', '', '', '', 0, '', 'mvcc', 'dvc56565', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2012-11-20 18:59:13', NULL, NULL, 43, 'agent', 'yes', NULL, NULL, 'inactive'),
(136, 149, 'arn009', '', '', '', '', '', '', '', '', 'owned', '', '2012-02-07', 'male', '', '', '', '', '', '', '', '', 0, '', 'mvcc', 'dvg34343', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2012-11-21 11:47:36', NULL, NULL, 43, 'agent', 'yes', NULL, NULL, 'inactive'),
(137, 150, '541254125', '', '', '', '', 'sunita', '', '', '', 'owned', '', '1977-11-20', 'male', '', 'tilak nagar', '', 'Mumbai', '', '', 'Maharashtra', 'IN', 400089, '', 'mvcc', 'ghfd4521', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'no', '', 'graduate', 2, '2012-11-21 12:56:50', '2012-11-21 13:00:02', NULL, 42, 'agent', 'no', '1', '1', 'inactive'),
(138, 151, 'arn99999', '01124545454', '', '', '', 'Sunita', '', '', '', 'owned', '', '2012-06-04', 'male', '', '787/89', 'Karol bagh', '', '', '', 'Maharashtra', 'IN', 226598, '', 'mvcc', 'der343', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'yes', 'two wheeler', 'graduate', 3, '2012-11-21 15:08:24', '2012-11-21 15:12:54', NULL, 43, 'agent', 'yes', '1', '1', 'inactive'),
(139, 152, 'arn8787787', '01124545454', '', '', '', 'Sunita', '', '', '', 'owned', '', '2012-02-01', 'male', '', '787/89', '', 'Akola', '', '', 'Maharashtra', 'IN', 223365, '', 'mvcc', 'der343', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'yes', 'two wheeler', 'intermediate', 2, '2012-11-21 15:44:53', '2012-11-21 15:45:24', NULL, 43, 'agent', 'no', NULL, NULL, 'inactive'),
(140, 153, 'ARN8787878', '01124547887', '', '', '', 'Sonia', '', '', '', 'owned', '', '2012-06-05', 'male', '', '787/97', 'Karol bagh', 'Ambivali Tarf Wankhal', '', '', 'Maharashtra', 'IN', 225488, '', 'mvcc', 'dfrerfasd', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'yes', 'two wheeler', 'graduate', 1, '2012-11-21 17:16:17', '2012-11-21 17:17:37', NULL, 43, 'agent', 'yes', '1', '1', 'inactive'),
(141, 154, 'ARN8789789', '898989898', '', '', '', 'Sonam', '', '', '', 'owned', '', '2012-05-07', 'male', '', '898/45', 'karol bagh', 'Akkalkot', '', '', 'Maharashtra', 'IN', 223354, '', 'mvcc', 'dfre43', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'yes', 'two wheeler', 'graduate', 3, '2012-11-21 18:28:30', '2012-11-21 18:30:51', NULL, 43, 'agent', 'yes', '1', '1', 'inactive'),
(142, 155, 'arn8787', '', '', '', '', '', '', '', '', 'owned', '', '2012-06-04', 'male', '', '', '', '', '', '', '', '', 0, '', 'mvcc', 'dsfas', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2012-11-21 18:44:08', NULL, NULL, 43, 'agent', 'yes', NULL, NULL, 'inactive'),
(143, 156, 'arn8787844', '9898989898', '', '', '', 'Neelima', '', '', '', 'owned', '', '2012-08-06', 'male', '', '5658/89', 'Tri na', 'Akkalkot', '', '', 'Maharashtra', 'IN', 226588, '', 'mvcc', 'sdfas', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'yes', 'two wheeler', 'intermediate', 2, '2012-11-21 18:54:32', '2012-11-21 19:02:08', NULL, 43, 'agent', 'yes', '1', '1', 'inactive'),
(144, 157, 'arn7687687', '5454545487', '', '', '', 'Rakhi', '', '', '', 'owned', '', '2012-06-04', 'male', '', '554/45', 'CP', 'Ahmedpur', '', '', 'Maharashtra', 'IN', 235656, '', 'mvcc', 'dfas', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'yes', 'car', 'graduate', 3, '2012-11-21 19:16:56', '2012-11-21 19:19:35', NULL, 43, 'agent', 'yes', '0', '1', 'inactive'),
(145, 158, 'arn78787', '1212121212', '', '', '', 'Anita', '', '', '', 'owned', '', '2012-01-04', 'male', '', '4387/454', 'Rooop Nagar', 'Ahmedpur', '', '', 'Maharashtra', 'IN', 223787, '', 'mvcc', 'dvc767', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'yes', 'car', 'graduate', 2, '2012-11-22 11:22:31', '2012-11-22 11:23:54', NULL, 43, 'agent', 'yes', '1', '1', 'inactive'),
(146, 159, 'arn787873', '1212121212', '', '', '', 'Anita', '', '', '', 'owned', '', '1985-06-04', 'male', '', '4387/454', 'Rooop Nagar', 'Akola', '', '', 'Maharashtra', 'IN', 223787, '', 'mvcc', 'sdfas', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'yes', 'two wheeler', 'graduate', 2, '2012-11-22 14:07:13', '2012-11-22 14:15:48', NULL, 43, 'agent', 'yes', '1', '0', 'inactive'),
(147, 160, 'arn873874', '1235678877', '', '', '', 'Sonam', '', '', '', 'owned', '', '1985-09-03', 'male', '', '3434/343', 'Ram Pura', 'Ajra', '', '', 'Maharashtra', 'IN', 225894, '', 'mvcc', 'sds', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'yes', 'two wheeler', 'graduate', 2, '2012-11-22 14:33:32', '2012-11-22 14:24:11', NULL, 43, 'agent', 'no', '1', '0', 'inactive'),
(148, 161, 'arn878783', '1212121221', '', '', '', 'Anita', '', '', '', 'owned', '', '1985-06-04', 'male', '', '78878/45', 'Hauz Khas', 'Akot', '', '', 'Maharashtra', 'IN', 225588, '', 'mvcc', 'dev33434', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'yes', 'two wheeler', 'graduate', 2, '2012-11-22 14:56:41', '2012-11-22 14:57:55', NULL, 43, 'agent', 'yes', '1', '1', 'inactive'),
(149, 162, 'arn8374763', '2121212121', '', '', '', 'Sonam', '', '', '', 'owned', '', '1985-08-06', 'male', '', '878/343', 'Karol Bagh', 'Ambivali Tarf Wankhal', '', '', 'Maharashtra', 'IN', 225589, '', 'mvcc', 'dfd33', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'yes', 'two wheeler', 'graduate', 1, '2012-11-22 15:01:52', '2012-11-22 15:02:32', NULL, 43, 'agent', 'no', '1', '0', 'inactive'),
(150, 163, 'arn8374733', '2121212121', '', '', '', 'Sonam', '', '', '', 'owned', '', '1985-07-02', 'male', '', '878/343', 'Karol Bagh', 'Alandi', '', '', 'Maharashtra', 'IN', 225589, '', 'mvcc', 'dfd33', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'yes', 'two wheeler', 'graduate', 2, '2012-11-22 15:04:33', '2012-11-22 15:05:07', NULL, 42, 'agent', 'yes', '1', '0', 'active'),
(151, 164, 'arn7878', '2121212112', '', '', '', 'Sunita', '', '', '', 'owned', '', '1980-11-11', 'male', '', '343/343', 'Karol bagh', 'Ahmedpur', '', '', 'Maharashtra', 'IN', 223478, '', 'mvcc', 'srfdas3', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'yes', 'two wheeler', 'graduate', 2, '2012-11-22 15:23:05', '2012-11-22 15:23:52', NULL, 42, 'agent', 'yes', NULL, NULL, 'active'),
(152, 165, 'arn8783', '2121221544', '', '', '', 'Sophiya', '', '', '', 'owned', '', '1980-01-08', 'male', '', '3434/34', 'Ashok Nagar', 'Akkalkot', '', '', 'Maharashtra', 'IN', 225882, '', 'mvcc', 'dvc343', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'yes', 'car', 'graduate', 2, '2012-11-22 15:36:07', '2012-11-22 15:37:05', NULL, 42, 'agent', 'yes', '1', '1', 'active'),
(153, 166, 'arn8998', '2121212154', '', '', '', 'Neelima', '', '', '', 'owned', '', '1985-06-03', 'male', '', '78734/34', 'Karol bagh', 'Ambivali Tarf Wankhal', '', '', 'Maharashtra', 'IN', 222873, '', 'mvcc', 'dv4', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'yes', 'car', 'graduate', 2, '2012-11-22 15:45:35', '2012-11-22 15:46:35', NULL, 42, 'agent', 'yes', '1', '1', 'active'),
(154, 167, 'arn989', '', '', '', '', '', '', '', '', 'owned', '', '1985-07-02', 'male', '', '', '', '', '', '', '', '', 0, '', 'mvcc', 'dev', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2012-11-22 16:59:45', NULL, NULL, 43, 'agent', 'yes', NULL, NULL, 'inactive'),
(155, 168, 'arn9897', '2145787878', '', '', '', 'sunita', '', '', '', 'owned', '', '1980-08-01', 'male', '', '989/34', 'Karol Bagh', '', '', '', 'Maharashtra', 'IN', 225587, '', 'mvcc', 'dev333', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'yes', 'two wheeler', 'graduate', 2, '2012-11-22 17:24:24', '2012-11-22 17:31:14', NULL, 43, 'agent', 'no', '1', '1', 'inactive'),
(156, 169, 'arn9892', '', '', '', '', '', '', '', '', 'owned', '', '0000-00-00', 'male', '', '', '', '', '', '', '', '', 0, '', 'mvcc', 'dev3', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2012-11-22 17:37:02', NULL, NULL, 43, 'agent', 'yes', NULL, NULL, 'inactive'),
(157, 170, 'arn878', '3434343434', '', '', '', 'Preeti', '', '', '', 'owned', '', '1985-07-02', 'male', '', '545/454', 'dfas', 'Ahmednagar', '', '', 'Maharashtra', 'IN', 225859, '', 'mvcc', 'dev343', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'yes', 'two wheeler', 'graduate', 1, '2012-11-23 11:21:21', '2012-11-23 11:22:17', NULL, 43, 'agent', 'no', '1', '0', 'active'),
(158, 171, 'arn78783', '3434343434', '', '', '', 'Maya', '', '', '', 'owned', '', '1980-10-02', 'male', '', '545/454', 'dfas', 'Ahmedpur', '', '', 'Maharashtra', 'IN', 225859, '', 'mvcc', 'dvc333', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'yes', 'car', 'graduate', 3, '2012-11-23 12:04:15', '2012-11-23 12:27:19', NULL, 43, 'agent', 'yes', '1', '1', 'active'),
(159, 172, 'arn783783', '3434343434', '', '', '', 'Maya', '', '', '', 'owned', '', '1958-11-07', 'male', '', '545/454', 'dfas', 'Ahmedpur', '', '', 'Maharashtra', 'IN', 225859, '', 'mvci', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'yes', 'two wheeler', 'graduate', 1, '2012-11-23 13:19:01', '2012-11-23 13:18:21', NULL, 43, 'agent', 'yes', NULL, NULL, 'inactive'),
(160, 173, 'arn8787343', '01112122121', '', '', '', 'Soniya', '', '', '', 'owned', '', '0000-00-00', 'male', '', '343/343', 'Ram Pura', 'Ajra', '', '', 'Maharashtra', 'IN', 223344, '', 'mvcc', 'sdfsd', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'yes', 'two wheeler', 'graduate', 1, '2012-11-23 14:36:48', '2012-11-23 14:37:54', NULL, 43, 'agent', 'yes', '1', '1', 'active'),
(161, 174, 'arn87872', '01112122121', '', '', '', 'Soniya', '', '', '', 'owned', '', '1988-10-03', 'male', '', '343/343', 'Ram Pura', 'Ahmedpur', '', '', 'Maharashtra', 'IN', 223344, '', 'mvcc', 'sss', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'yes', 'two wheeler', 'graduate', 1, '2012-11-23 16:56:49', '2012-11-23 16:03:15', NULL, 43, 'agent', 'no', NULL, NULL, 'inactive');

-- --------------------------------------------------------

--
-- Table structure for table `t_cardholder_offers`
--

CREATE TABLE IF NOT EXISTS `t_cardholder_offers` (
  `cardholder_id` int(11) NOT NULL,
  `is_book` enum('1','0') CHARACTER SET utf8 NOT NULL,
  `is_travel` enum('1','0') CHARACTER SET utf8 NOT NULL,
  `is_movies` enum('1','0') CHARACTER SET utf8 NOT NULL,
  `is_shopping` enum('1','0') CHARACTER SET utf8 NOT NULL,
  `is_electronics` enum('1','0') CHARACTER SET utf8 NOT NULL,
  `is_music` enum('1','0') CHARACTER SET utf8 NOT NULL,
  `is_automobiles` enum('1','0') CHARACTER SET utf8 NOT NULL,
  `date_created` datetime NOT NULL,
  `status` enum('active','inactive') CHARACTER SET utf8 NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `t_cardholder_offers`
--

INSERT INTO `t_cardholder_offers` (`cardholder_id`, `is_book`, `is_travel`, `is_movies`, `is_shopping`, `is_electronics`, `is_music`, `is_automobiles`, `date_created`, `status`) VALUES
(128, '0', '1', '1', '1', '0', '0', '0', '2012-11-17 12:01:30', 'active'),
(130, '0', '0', '1', '1', '0', '0', '0', '2012-11-17 12:40:57', 'active'),
(131, '1', '1', '0', '0', '0', '0', '0', '2012-11-19 12:34:11', 'active'),
(132, '1', '1', '0', '0', '0', '0', '0', '2012-11-19 12:45:34', 'active'),
(136, '1', '1', '0', '0', '0', '0', '0', '2012-11-19 14:22:55', 'active'),
(137, '1', '1', '1', '0', '0', '0', '0', '2012-11-19 14:53:54', 'active'),
(139, '1', '1', '0', '0', '0', '0', '0', '2012-11-19 15:45:12', 'active'),
(140, '1', '1', '0', '0', '0', '0', '0', '2012-11-19 15:55:13', 'active'),
(141, '0', '0', '1', '1', '0', '0', '0', '2012-11-19 16:30:41', 'active'),
(142, '1', '1', '1', '1', '0', '0', '0', '2012-11-19 19:01:02', 'active'),
(143, '0', '0', '1', '1', '0', '0', '0', '2012-11-20 14:17:20', 'active'),
(145, '1', '0', '1', '0', '0', '0', '0', '2012-11-20 15:53:52', 'active'),
(146, '1', '1', '0', '0', '0', '0', '0', '2012-11-20 16:51:01', 'active'),
(147, '0', '1', '1', '0', '0', '0', '0', '2012-11-20 17:01:02', 'active'),
(150, '0', '0', '0', '0', '0', '0', '0', '2012-11-21 13:00:02', 'active'),
(151, '1', '1', '0', '0', '0', '0', '0', '2012-11-21 15:09:40', 'active'),
(151, '1', '1', '0', '0', '0', '0', '0', '2012-11-21 15:12:54', 'active'),
(153, '1', '1', '1', '0', '0', '0', '0', '2012-11-21 17:17:38', 'active'),
(154, '0', '0', '1', '1', '0', '0', '0', '2012-11-21 18:30:52', 'active'),
(156, '1', '1', '0', '0', '0', '0', '0', '2012-11-21 19:02:08', 'active'),
(157, '0', '1', '1', '0', '0', '0', '0', '2012-11-21 19:19:35', 'active'),
(158, '1', '1', '0', '0', '0', '0', '0', '2012-11-22 11:23:54', 'active'),
(161, '1', '1', '1', '0', '0', '0', '0', '2012-11-22 14:57:55', 'active'),
(163, '1', '1', '0', '0', '0', '0', '0', '2012-11-22 15:05:07', 'active'),
(164, '1', '1', '0', '0', '0', '0', '0', '2012-11-22 15:23:52', 'active'),
(165, '1', '1', '1', '1', '0', '0', '0', '2012-11-22 15:37:05', 'active'),
(166, '0', '0', '0', '1', '0', '0', '0', '2012-11-22 15:46:35', 'active'),
(171, '0', '1', '1', '0', '0', '0', '0', '2012-11-23 12:27:19', 'active'),
(173, '0', '0', '0', '0', '1', '1', '0', '2012-11-23 14:37:54', 'active');

-- --------------------------------------------------------

--
-- Table structure for table `t_cardholder_transactions`
--

CREATE TABLE IF NOT EXISTS `t_cardholder_transactions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cardholder_id` int(11) NOT NULL,
  `agent_id` int(11) NOT NULL,
  `amount` decimal(9,4) NOT NULL,
  `mode` enum('cr','dr') NOT NULL DEFAULT 'cr',
  `trans_type` varchar(20) DEFAULT NULL,
  `date_created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=88 ;

--
-- Dumping data for table `t_cardholder_transactions`
--

INSERT INTO `t_cardholder_transactions` (`id`, `cardholder_id`, `agent_id`, `amount`, `mode`, `trans_type`, `date_created`) VALUES
(1, 0, 0, 0.0000, '', NULL, '0000-00-00 00:00:00'),
(2, 0, 0, 0.0000, '', NULL, '0000-00-00 00:00:00'),
(3, 3, 43, 200.0000, 'cr', NULL, '2012-11-07 11:02:43'),
(4, 3, 43, 500.0000, 'cr', NULL, '2012-11-07 11:02:55'),
(5, 3, 43, 10.0000, 'cr', NULL, '2012-11-07 11:07:06'),
(6, 3, 43, 200.0000, 'cr', NULL, '2012-11-07 11:11:13'),
(7, 0, 43, 90.0000, 'cr', NULL, '2012-11-07 12:57:03'),
(8, 0, 43, 90.0000, 'cr', NULL, '2012-11-07 12:57:21'),
(9, 0, 43, 90.0000, 'cr', NULL, '2012-11-07 12:57:51'),
(10, 0, 43, 90.0000, 'cr', NULL, '2012-11-07 12:58:41'),
(11, 0, 43, 90.0000, 'cr', NULL, '2012-11-07 13:01:37'),
(12, 0, 43, 200.0000, 'cr', NULL, '2012-11-07 13:02:10'),
(13, 0, 43, 40.0000, 'cr', NULL, '2012-11-07 13:05:09'),
(14, 0, 43, 500.0000, 'cr', NULL, '2012-11-07 13:06:41'),
(15, 0, 43, 500.0000, 'cr', NULL, '2012-11-07 13:07:08'),
(16, 0, 43, 500.0000, 'cr', NULL, '2012-11-07 13:07:24'),
(17, 0, 43, 500.0000, 'cr', NULL, '2012-11-07 13:07:49'),
(18, 0, 43, 500.0000, 'cr', NULL, '2012-11-07 13:07:56'),
(19, 0, 43, 500.0000, 'cr', NULL, '2012-11-07 13:08:28'),
(20, 0, 43, 500.0000, 'cr', NULL, '2012-11-07 13:08:43'),
(21, 0, 43, 500.0000, 'cr', NULL, '2012-11-07 13:10:04'),
(22, 0, 43, 500.0000, 'cr', NULL, '2012-11-07 13:11:34'),
(23, 0, 43, 500.0000, 'cr', NULL, '2012-11-07 13:12:14'),
(24, 0, 43, 500.0000, 'cr', NULL, '2012-11-07 13:12:37'),
(25, 0, 43, 400.0000, 'cr', NULL, '2012-11-07 13:25:39'),
(26, 0, 43, 400.0000, 'cr', NULL, '2012-11-07 13:27:39'),
(27, 0, 43, 400.0000, 'cr', NULL, '2012-11-07 13:28:32'),
(28, 0, 43, 400.0000, 'cr', NULL, '2012-11-07 13:28:45'),
(29, 0, 43, 400.0000, 'cr', NULL, '2012-11-07 13:32:47'),
(30, 0, 43, 100.0000, 'cr', NULL, '2012-11-07 13:33:46'),
(31, 0, 43, 100.0000, 'cr', NULL, '2012-11-07 13:36:15'),
(32, 0, 43, 100.0000, 'cr', NULL, '2012-11-07 13:37:01'),
(33, 0, 43, 100.0000, 'cr', NULL, '2012-11-07 13:37:20'),
(34, 0, 43, 100.0000, 'cr', NULL, '2012-11-07 13:38:39'),
(35, 0, 43, 100.0000, 'cr', NULL, '2012-11-07 13:44:08'),
(36, 0, 43, 100.0000, 'cr', NULL, '2012-11-07 13:47:15'),
(37, 0, 43, 100.0000, 'cr', NULL, '2012-11-07 13:48:52'),
(38, 0, 43, 100.0000, 'cr', NULL, '2012-11-07 13:49:34'),
(39, 0, 43, 100.0000, 'cr', NULL, '2012-11-07 13:51:35'),
(40, 0, 43, 100.0000, 'cr', NULL, '2012-11-07 13:53:40'),
(41, 0, 43, 100.0000, 'cr', NULL, '2012-11-07 13:55:34'),
(42, 0, 43, 150.0000, 'cr', NULL, '2012-11-07 13:56:17'),
(43, 0, 43, 50.0000, 'cr', NULL, '2012-11-07 14:09:38'),
(44, 0, 43, 50.0000, 'cr', NULL, '2012-11-07 14:11:18'),
(45, 0, 43, 50.0000, 'cr', NULL, '2012-11-07 14:12:20'),
(46, 0, 43, 50.0000, 'cr', NULL, '2012-11-07 14:16:17'),
(47, 0, 43, 50.0000, 'cr', NULL, '2012-11-07 14:16:43'),
(48, 0, 43, 50.0000, 'cr', NULL, '2012-11-07 14:17:47'),
(49, 0, 43, 333.0000, 'cr', NULL, '2012-11-07 14:26:09'),
(50, 0, 43, 333.0000, 'cr', NULL, '2012-11-07 14:26:42'),
(51, 0, 43, 333.0000, 'cr', NULL, '2012-11-07 14:29:47'),
(52, 0, 43, 300.0000, 'cr', NULL, '2012-11-07 14:34:41'),
(53, 3, 43, 500.0000, 'cr', NULL, '2012-11-07 15:14:33'),
(54, 0, 43, 51.0000, 'cr', NULL, '2012-11-07 15:23:56'),
(55, 0, 43, 200.0000, 'cr', NULL, '2012-11-07 15:26:30'),
(56, 3, 43, 100.0000, 'cr', NULL, '2012-11-07 15:33:50'),
(57, 1, 43, 1123.0000, 'cr', NULL, '2012-11-07 15:52:23'),
(58, 3, 43, 33.0000, 'cr', NULL, '2012-11-07 16:55:40'),
(59, 3, 43, 144.0000, 'cr', NULL, '2012-11-07 16:57:59'),
(60, 3, 43, 100.0000, 'cr', NULL, '2012-11-07 17:16:49'),
(61, 3, 43, 300.0000, 'cr', NULL, '2012-11-07 17:23:29'),
(62, 3, 43, 0.0000, 'cr', NULL, '2012-11-07 17:26:05'),
(63, 130, 43, 100.0000, 'cr', 'CDRG', '2012-11-17 13:41:42'),
(64, 132, 43, 100.0000, 'cr', 'CDRG', '2012-11-19 13:04:18'),
(65, 133, 43, 1200.0000, 'cr', 'CDRG', '2012-11-19 13:18:24'),
(66, 134, 43, 1000.0000, 'cr', 'CDRG', '2012-11-19 13:34:30'),
(67, 135, 43, 3000.0000, 'cr', 'CDRG', '2012-11-19 13:39:20'),
(68, 136, 43, 1000.0000, 'cr', 'CDRG', '2012-11-19 14:23:37'),
(69, 137, 43, 3000.0000, 'cr', 'CDRG', '2012-11-19 14:58:02'),
(70, 139, 43, 2000.0000, 'cr', 'CDRG', '2012-11-19 15:46:08'),
(71, 140, 43, 1000.0000, 'cr', 'CDRG', '2012-11-19 15:55:34'),
(72, 141, 43, 1000.0000, 'cr', 'CDRG', '2012-11-19 16:30:59'),
(73, 142, 83, 500.0000, 'cr', 'CDRG', '2012-11-19 19:12:37'),
(74, 143, 43, 2000.0000, 'cr', 'CDRG', '2012-11-20 14:18:07'),
(75, 150, 42, 1000.0000, 'cr', 'CDRG', '2012-11-21 13:01:02'),
(76, 151, 43, 1000.0000, 'cr', 'CDRG', '2012-11-21 15:13:25'),
(77, 154, 43, 2000.0000, 'cr', 'CDRG', '2012-11-21 18:32:27'),
(78, 157, 43, 1500.0000, 'cr', 'CDRG', '2012-11-21 19:49:33'),
(79, 158, 43, 2300.0000, 'cr', 'CDRG', '2012-11-22 12:31:31'),
(80, 159, 43, 2000.0000, 'cr', 'CDRG', '2012-11-22 14:16:39'),
(81, 162, 43, 1500.0000, 'cr', 'CDRG', '2012-11-22 15:17:05'),
(82, 165, 43, 1200.0000, 'cr', 'CDRG', '2012-11-22 15:38:09'),
(83, 166, 43, 1500.0000, 'cr', 'CDRG', '2012-11-22 15:47:16'),
(84, 168, 43, 1500.0000, 'cr', 'CDRG', '2012-11-22 17:32:52'),
(85, 170, 43, 1500.0000, 'cr', 'CDRG', '2012-11-23 11:25:04'),
(86, 171, 43, 1000.0000, 'cr', 'CDRG', '2012-11-23 12:28:01'),
(87, 173, 43, 1200.0000, 'cr', 'CDRG', '2012-11-23 15:31:18');

-- --------------------------------------------------------

--
-- Table structure for table `t_countries`
--

CREATE TABLE IF NOT EXISTS `t_countries` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` char(2) NOT NULL COMMENT 'Two-letter country code (ISO 3166-1 alpha-2)',
  `name` varchar(64) NOT NULL COMMENT 'English country name',
  `full_name` varchar(128) NOT NULL COMMENT 'Full English country name',
  `iso3` char(3) NOT NULL COMMENT 'Three-letter country code (ISO 3166-1 alpha-3)',
  `number` smallint(3) unsigned zerofill NOT NULL COMMENT 'Three-digit country number (ISO 3166-1 numeric)',
  `continent_code` char(2) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_code` (`code`) USING BTREE,
  KEY `idx_continent_code` (`continent_code`) USING BTREE
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=247 ;

--
-- Dumping data for table `t_countries`
--

INSERT INTO `t_countries` (`id`, `code`, `name`, `full_name`, `iso3`, `number`, `continent_code`) VALUES
(1, 'AD', 'Andorra', 'Principality of Andorra', 'AND', 020, 'EU'),
(2, 'AE', 'United Arab Emirates', 'United Arab Emirates', 'ARE', 784, 'AS'),
(3, 'AF', 'Afghanistan', 'Islamic Republic of Afghanistan', 'AFG', 004, 'AS'),
(4, 'AG', 'Antigua and Barbuda', 'Antigua and Barbuda', 'ATG', 028, 'NA'),
(5, 'AI', 'Anguilla', 'Anguilla', 'AIA', 660, 'NA'),
(6, 'AL', 'Albania', 'Republic of Albania', 'ALB', 008, 'EU'),
(7, 'AM', 'Armenia', 'Republic of Armenia', 'ARM', 051, 'AS'),
(8, 'AN', 'Netherlands Antilles', 'Netherlands Antilles', 'ANT', 530, 'NA'),
(9, 'AO', 'Angola', 'Republic of Angola', 'AGO', 024, 'AF'),
(10, 'AQ', 'Antarctica', 'Antarctica (the territory South of 60 deg S)', 'ATA', 010, 'AN'),
(11, 'AR', 'Argentina', 'Argentine Republic', 'ARG', 032, 'SA'),
(12, 'AS', 'American Samoa', 'American Samoa', 'ASM', 016, 'OC'),
(13, 'AT', 'Austria', 'Republic of Austria', 'AUT', 040, 'EU'),
(14, 'AU', 'Australia', 'Commonwealth of Australia', 'AUS', 036, 'OC'),
(15, 'AW', 'Aruba', 'Aruba', 'ABW', 533, 'NA'),
(17, 'AZ', 'Azerbaijan', 'Republic of Azerbaijan', 'AZE', 031, 'AS'),
(18, 'BA', 'Bosnia and Herzegovina', 'Bosnia and Herzegovina', 'BIH', 070, 'EU'),
(19, 'BB', 'Barbados', 'Barbados', 'BRB', 052, 'NA'),
(20, 'BD', 'Bangladesh', 'People''s Republic of Bangladesh', 'BGD', 050, 'AS'),
(21, 'BE', 'Belgium', 'Kingdom of Belgium', 'BEL', 056, 'EU'),
(22, 'BF', 'Burkina Faso', 'Burkina Faso', 'BFA', 854, 'AF'),
(23, 'BG', 'Bulgaria', 'Republic of Bulgaria', 'BGR', 100, 'EU'),
(24, 'BH', 'Bahrain', 'Kingdom of Bahrain', 'BHR', 048, 'AS'),
(25, 'BI', 'Burundi', 'Republic of Burundi', 'BDI', 108, 'AF'),
(26, 'BJ', 'Benin', 'Republic of Benin', 'BEN', 204, 'AF'),
(28, 'BM', 'Bermuda', 'Bermuda', 'BMU', 060, 'NA'),
(29, 'BN', 'Brunei Darussalam', 'Brunei Darussalam', 'BRN', 096, 'AS'),
(30, 'BO', 'Bolivia', 'Republic of Bolivia', 'BOL', 068, 'SA'),
(31, 'BR', 'Brazil', 'Federative Republic of Brazil', 'BRA', 076, 'SA'),
(32, 'BS', 'Bahamas', 'Commonwealth of the Bahamas', 'BHS', 044, 'NA'),
(33, 'BT', 'Bhutan', 'Kingdom of Bhutan', 'BTN', 064, 'AS'),
(34, 'BV', 'Bouvet Island', 'Bouvet Island (Bouvetoya)', 'BVT', 074, 'AN'),
(35, 'BW', 'Botswana', 'Republic of Botswana', 'BWA', 072, 'AF'),
(36, 'BY', 'Belarus', 'Republic of Belarus', 'BLR', 112, 'EU'),
(37, 'BZ', 'Belize', 'Belize', 'BLZ', 084, 'NA'),
(38, 'CA', 'Canada', 'Canada', 'CAN', 124, 'NA'),
(39, 'CC', 'Cocos (Keeling) Islands', 'Cocos (Keeling) Islands', 'CCK', 166, 'AS'),
(40, 'CD', 'Congo (Kinshasa)', 'Democratic Republic of the Congo', 'COD', 180, 'AF'),
(41, 'CF', 'Central African Republic', 'Central African Republic', 'CAF', 140, 'AF'),
(42, 'CG', 'Congo (Brazzaville)', 'Republic of the Congo', 'COG', 178, 'AF'),
(43, 'CH', 'Switzerland', 'Swiss Confederation', 'CHE', 756, 'EU'),
(45, 'CK', 'Cook Islands', 'Cook Islands', 'COK', 184, 'OC'),
(46, 'CL', 'Chile', 'Republic of Chile', 'CHL', 152, 'SA'),
(47, 'CM', 'Cameroon', 'Republic of Cameroon', 'CMR', 120, 'AF'),
(48, 'CN', 'China', 'People''s Republic of China', 'CHN', 156, 'AS'),
(49, 'CO', 'Colombia', 'Republic of Colombia', 'COL', 170, 'SA'),
(50, 'CR', 'Costa Rica', 'Republic of Costa Rica', 'CRI', 188, 'NA'),
(51, 'CU', 'Cuba', 'Republic of Cuba', 'CUB', 192, 'NA'),
(52, 'CV', 'Cape Verde', 'Republic of Cape Verde', 'CPV', 132, 'AF'),
(53, 'CX', 'Christmas Island', 'Christmas Island', 'CXR', 162, 'AS'),
(54, 'CY', 'Cyprus', 'Republic of Cyprus', 'CYP', 196, 'AS'),
(55, 'CZ', 'Czech Republic', 'Czech Republic', 'CZE', 203, 'EU'),
(56, 'DE', 'Germany', 'Federal Republic of Germany', 'DEU', 276, 'EU'),
(57, 'DJ', 'Djibouti', 'Republic of Djibouti', 'DJI', 262, 'AF'),
(58, 'DK', 'Denmark', 'Kingdom of Denmark', 'DNK', 208, 'EU'),
(59, 'DM', 'Dominica', 'Commonwealth of Dominica', 'DMA', 212, 'NA'),
(60, 'DO', 'Dominican Republic', 'Dominican Republic', 'DOM', 214, 'NA'),
(61, 'DZ', 'Algeria', 'People''s Democratic Republic of Algeria', 'DZA', 012, 'AF'),
(62, 'EC', 'Ecuador', 'Republic of Ecuador', 'ECU', 218, 'SA'),
(63, 'EE', 'Estonia', 'Republic of Estonia', 'EST', 233, 'EU'),
(64, 'EG', 'Egypt', 'Arab Republic of Egypt', 'EGY', 818, 'AF'),
(65, 'EH', 'Western Sahara', 'Western Sahara', 'ESH', 732, 'AF'),
(66, 'ER', 'Eritrea', 'State of Eritrea', 'ERI', 232, 'AF'),
(67, 'ES', 'Spain', 'Kingdom of Spain', 'ESP', 724, 'EU'),
(68, 'ET', 'Ethiopia', 'Federal Democratic Republic of Ethiopia', 'ETH', 231, 'AF'),
(69, 'FI', 'Finland', 'Republic of Finland', 'FIN', 246, 'EU'),
(70, 'FJ', 'Fiji', 'Republic of the Fiji Islands', 'FJI', 242, 'OC'),
(71, 'FK', 'Falkland Islands', 'Falkland Islands (Malvinas)', 'FLK', 238, 'SA'),
(72, 'FM', 'Micronesia', 'Federated States of Micronesia', 'FSM', 583, 'OC'),
(73, 'FO', 'Faroe Islands', 'Faroe Islands', 'FRO', 234, 'EU'),
(74, 'FR', 'France', 'French Republic', 'FRA', 250, 'EU'),
(75, 'GA', 'Gabon', 'Gabonese Republic', 'GAB', 266, 'AF'),
(76, 'GB', 'United Kingdom', 'United Kingdom of Great Britain & Northern Ireland', 'GBR', 826, 'EU'),
(77, 'GD', 'Grenada', 'Grenada', 'GRD', 308, 'NA'),
(78, 'GE', 'Georgia', 'Georgia', 'GEO', 268, 'AS'),
(79, 'GF', 'French Guiana', 'French Guiana', 'GUF', 254, 'SA'),
(80, 'GG', 'Guernsey', 'Bailiwick of Guernsey', 'GGY', 831, 'EU'),
(81, 'GH', 'Ghana', 'Republic of Ghana', 'GHA', 288, 'AF'),
(82, 'GI', 'Gibraltar', 'Gibraltar', 'GIB', 292, 'EU'),
(83, 'GL', 'Greenland', 'Greenland', 'GRL', 304, 'NA'),
(84, 'GM', 'Gambia', 'Republic of the Gambia', 'GMB', 270, 'AF'),
(85, 'GN', 'Guinea', 'Republic of Guinea', 'GIN', 324, 'AF'),
(86, 'GP', 'Guadeloupe', 'Guadeloupe', 'GLP', 312, 'NA'),
(87, 'GQ', 'Equatorial Guinea', 'Republic of Equatorial Guinea', 'GNQ', 226, 'AF'),
(88, 'GR', 'Greece', 'Hellenic Republic Greece', 'GRC', 300, 'EU'),
(89, 'GS', 'South Georgia and South Sandwich Islands', 'South Georgia and the South Sandwich Islands', 'SGS', 239, 'AN'),
(90, 'GT', 'Guatemala', 'Republic of Guatemala', 'GTM', 320, 'NA'),
(91, 'GU', 'Guam', 'Guam', 'GUM', 316, 'OC'),
(92, 'GW', 'Guinea-Bissau', 'Republic of Guinea-Bissau', 'GNB', 624, 'AF'),
(93, 'GY', 'Guyana', 'Co-operative Republic of Guyana', 'GUY', 328, 'SA'),
(94, 'HK', 'Hong Kong', 'Hong Kong Special Administrative Region of China', 'HKG', 344, 'AS'),
(95, 'HM', 'Heard and McDonald Islands', 'Heard Island and McDonald Islands', 'HMD', 334, 'AN'),
(96, 'HN', 'Honduras', 'Republic of Honduras', 'HND', 340, 'NA'),
(97, 'HR', 'Croatia', 'Republic of Croatia', 'HRV', 191, 'EU'),
(98, 'HT', 'Haiti', 'Republic of Haiti', 'HTI', 332, 'NA'),
(99, 'HU', 'Hungary', 'Republic of Hungary', 'HUN', 348, 'EU'),
(100, 'ID', 'Indonesia', 'Republic of Indonesia', 'IDN', 360, 'AS'),
(101, 'IE', 'Ireland', 'Ireland', 'IRL', 372, 'EU'),
(102, 'IL', 'Israel', 'State of Israel', 'ISR', 376, 'AS'),
(103, 'IM', 'Isle of Man', 'Isle of Man', 'IMN', 833, 'EU'),
(104, 'IN', 'India', 'Republic of India', 'IND', 356, 'AS'),
(105, 'IO', 'British Indian Ocean Territory', 'British Indian Ocean Territory (Chagos Archipelago)', 'IOT', 086, 'AS'),
(106, 'IQ', 'Iraq', 'Republic of Iraq', 'IRQ', 368, 'AS'),
(107, 'IR', 'Iran', 'Islamic Republic of Iran', 'IRN', 364, 'AS'),
(108, 'IS', 'Iceland', 'Republic of Iceland', 'ISL', 352, 'EU'),
(109, 'IT', 'Italy', 'Italian Republic', 'ITA', 380, 'EU'),
(110, 'JE', 'Jersey', 'Bailiwick of Jersey', 'JEY', 832, 'EU'),
(111, 'JM', 'Jamaica', 'Jamaica', 'JAM', 388, 'NA'),
(112, 'JO', 'Jordan', 'Hashemite Kingdom of Jordan', 'JOR', 400, 'AS'),
(113, 'JP', 'Japan', 'Japan', 'JPN', 392, 'AS'),
(114, 'KE', 'Kenya', 'Republic of Kenya', 'KEN', 404, 'AF'),
(115, 'KG', 'Kyrgyzstan', 'Kyrgyz Republic', 'KGZ', 417, 'AS'),
(116, 'KH', 'Cambodia', 'Kingdom of Cambodia', 'KHM', 116, 'AS'),
(117, 'KI', 'Kiribati', 'Republic of Kiribati', 'KIR', 296, 'OC'),
(118, 'KM', 'Comoros', 'Union of the Comoros', 'COM', 174, 'AF'),
(119, 'KN', 'Saint Kitts and Nevis', 'Federation of Saint Kitts and Nevis', 'KNA', 659, 'NA'),
(120, 'KP', 'Korea, North', 'Democratic People''s Republic of Korea', 'PRK', 408, 'AS'),
(121, 'KR', 'Korea, South', 'Republic of Korea', 'KOR', 410, 'AS'),
(122, 'KW', 'Kuwait', 'State of Kuwait', 'KWT', 414, 'AS'),
(123, 'KY', 'Cayman Islands', 'Cayman Islands', 'CYM', 136, 'NA'),
(124, 'KZ', 'Kazakhstan', 'Republic of Kazakhstan', 'KAZ', 398, 'AS'),
(125, 'LA', 'Laos', 'Lao People''s Democratic Republic', 'LAO', 418, 'AS'),
(126, 'LB', 'Lebanon', 'Lebanese Republic', 'LBN', 422, 'AS'),
(127, 'LC', 'Saint Lucia', 'Saint Lucia', 'LCA', 662, 'NA'),
(128, 'LI', 'Liechtenstein', 'Principality of Liechtenstein', 'LIE', 438, 'EU'),
(129, 'LK', 'Sri Lanka', 'Democratic Socialist Republic of Sri Lanka', 'LKA', 144, 'AS'),
(130, 'LR', 'Liberia', 'Republic of Liberia', 'LBR', 430, 'AF'),
(131, 'LS', 'Lesotho', 'Kingdom of Lesotho', 'LSO', 426, 'AF'),
(132, 'LT', 'Lithuania', 'Republic of Lithuania', 'LTU', 440, 'EU'),
(133, 'LU', 'Luxembourg', 'Grand Duchy of Luxembourg', 'LUX', 442, 'EU'),
(134, 'LV', 'Latvia', 'Republic of Latvia', 'LVA', 428, 'EU'),
(135, 'LY', 'Libya', 'Libyan Arab Jamahiriya', 'LBY', 434, 'AF'),
(136, 'MA', 'Morocco', 'Kingdom of Morocco', 'MAR', 504, 'AF'),
(137, 'MC', 'Monaco', 'Principality of Monaco', 'MCO', 492, 'EU'),
(138, 'MD', 'Moldova', 'Republic of Moldova', 'MDA', 498, 'EU'),
(139, 'ME', 'Montenegro', 'Republic of Montenegro', 'MNE', 499, 'EU'),
(140, 'MF', 'Saint Martin (French part)', 'Saint Martin', 'MAF', 663, 'NA'),
(141, 'MG', 'Madagascar', 'Republic of Madagascar', 'MDG', 450, 'AF'),
(142, 'MH', 'Marshall Islands', 'Republic of the Marshall Islands', 'MHL', 584, 'OC'),
(143, 'MK', 'Macedonia', 'Republic of Macedonia', 'MKD', 807, 'EU'),
(144, 'ML', 'Mali', 'Republic of Mali', 'MLI', 466, 'AF'),
(145, 'MM', 'Myanmar', 'Union of Myanmar', 'MMR', 104, 'AS'),
(146, 'MN', 'Mongolia', 'Mongolia', 'MNG', 496, 'AS'),
(147, 'MO', 'Macau', 'Macao Special Administrative Region of China', 'MAC', 446, 'AS'),
(148, 'MP', 'Northern Mariana Islands', 'Commonwealth of the Northern Mariana Islands', 'MNP', 580, 'OC'),
(149, 'MQ', 'Martinique', 'Martinique', 'MTQ', 474, 'NA'),
(150, 'MR', 'Mauritania', 'Islamic Republic of Mauritania', 'MRT', 478, 'AF'),
(151, 'MS', 'Montserrat', 'Montserrat', 'MSR', 500, 'NA'),
(152, 'MT', 'Malta', 'Republic of Malta', 'MLT', 470, 'EU'),
(153, 'MU', 'Mauritius', 'Republic of Mauritius', 'MUS', 480, 'AF'),
(154, 'MV', 'Maldives', 'Republic of Maldives', 'MDV', 462, 'AS'),
(155, 'MW', 'Malawi', 'Republic of Malawi', 'MWI', 454, 'AF'),
(156, 'MX', 'Mexico', 'United Mexican States', 'MEX', 484, 'NA'),
(157, 'MY', 'Malaysia', 'Malaysia', 'MYS', 458, 'AS'),
(158, 'MZ', 'Mozambique', 'Republic of Mozambique', 'MOZ', 508, 'AF'),
(159, 'NA', 'Namibia', 'Republic of Namibia', 'NAM', 516, 'AF'),
(160, 'NC', 'New Caledonia', 'New Caledonia', 'NCL', 540, 'OC'),
(161, 'NE', 'Niger', 'Republic of Niger', 'NER', 562, 'AF'),
(162, 'NF', 'Norfolk Island', 'Norfolk Island', 'NFK', 574, 'OC'),
(163, 'NG', 'Nigeria', 'Federal Republic of Nigeria', 'NGA', 566, 'AF'),
(164, 'NI', 'Nicaragua', 'Republic of Nicaragua', 'NIC', 558, 'NA'),
(165, 'NL', 'Netherlands', 'Kingdom of the Netherlands', 'NLD', 528, 'EU'),
(166, 'NO', 'Norway', 'Kingdom of Norway', 'NOR', 578, 'EU'),
(167, 'NP', 'Nepal', 'State of Nepal', 'NPL', 524, 'AS'),
(168, 'NR', 'Nauru', 'Republic of Nauru', 'NRU', 520, 'OC'),
(169, 'NU', 'Niue', 'Niue', 'NIU', 570, 'OC'),
(170, 'NZ', 'New Zealand', 'New Zealand', 'NZL', 554, 'OC'),
(171, 'OM', 'Oman', 'Sultanate of Oman', 'OMN', 512, 'AS'),
(172, 'PA', 'Panama', 'Republic of Panama', 'PAN', 591, 'NA'),
(173, 'PE', 'Peru', 'Republic of Peru', 'PER', 604, 'SA'),
(174, 'PF', 'French Polynesia', 'French Polynesia', 'PYF', 258, 'OC'),
(175, 'PG', 'Papua New Guinea', 'Independent State of Papua New Guinea', 'PNG', 598, 'OC'),
(176, 'PH', 'Philippines', 'Republic of the Philippines', 'PHL', 608, 'AS'),
(177, 'PK', 'Pakistan', 'Islamic Republic of Pakistan', 'PAK', 586, 'AS'),
(178, 'PL', 'Poland', 'Republic of Poland', 'POL', 616, 'EU'),
(179, 'PM', 'Saint Pierre and Miquelon', 'Saint Pierre and Miquelon', 'SPM', 666, 'NA'),
(180, 'PN', 'Pitcairn', 'Pitcairn Islands', 'PCN', 612, 'OC'),
(181, 'PR', 'Puerto Rico', 'Commonwealth of Puerto Rico', 'PRI', 630, 'NA'),
(182, 'PS', 'Palestine', 'Occupied Palestinian Territory', 'PSE', 275, 'AS'),
(183, 'PT', 'Portugal', 'Portuguese Republic', 'PRT', 620, 'EU'),
(184, 'PW', 'Palau', 'Republic of Palau', 'PLW', 585, 'OC'),
(185, 'PY', 'Paraguay', 'Republic of Paraguay', 'PRY', 600, 'SA'),
(186, 'QA', 'Qatar', 'State of Qatar', 'QAT', 634, 'AS'),
(187, 'RE', 'Reunion', 'Reunion', 'REU', 638, 'AF'),
(188, 'RO', 'Romania', 'Romania', 'ROU', 642, 'EU'),
(189, 'RS', 'Serbia', 'Republic of Serbia', 'SRB', 688, 'EU'),
(190, 'RU', 'Russian Federation', 'Russian Federation', 'RUS', 643, 'EU'),
(191, 'RW', 'Rwanda', 'Republic of Rwanda', 'RWA', 646, 'AF'),
(192, 'SA', 'Saudi Arabia', 'Kingdom of Saudi Arabia', 'SAU', 682, 'AS'),
(193, 'SB', 'Solomon Islands', 'Solomon Islands', 'SLB', 090, 'OC'),
(194, 'SC', 'Seychelles', 'Republic of Seychelles', 'SYC', 690, 'AF'),
(195, 'SD', 'Sudan', 'Republic of Sudan', 'SDN', 736, 'AF'),
(196, 'SE', 'Sweden', 'Kingdom of Sweden', 'SWE', 752, 'EU'),
(197, 'SG', 'Singapore', 'Republic of Singapore', 'SGP', 702, 'AS'),
(198, 'SH', 'Saint Helena', 'Saint Helena', 'SHN', 654, 'AF'),
(199, 'SI', 'Slovenia', 'Republic of Slovenia', 'SVN', 705, 'EU'),
(200, 'SJ', 'Svalbard and Jan Mayen Islands', 'Svalbard & Jan Mayen Islands', 'SJM', 744, 'EU'),
(201, 'SK', 'Slovakia', 'Slovakia (Slovak Republic)', 'SVK', 703, 'EU'),
(202, 'SL', 'Sierra Leone', 'Republic of Sierra Leone', 'SLE', 694, 'AF'),
(203, 'SM', 'San Marino', 'Republic of San Marino', 'SMR', 674, 'EU'),
(204, 'SN', 'Senegal', 'Republic of Senegal', 'SEN', 686, 'AF'),
(205, 'SO', 'Somalia', 'Somali Republic', 'SOM', 706, 'AF'),
(206, 'SR', 'Suriname', 'Republic of Suriname', 'SUR', 740, 'SA'),
(207, 'ST', 'Sao Tome and Principe', 'Democratic Republic of Sao Tome and Principe', 'STP', 678, 'AF'),
(208, 'SV', 'El Salvador', 'Republic of El Salvador', 'SLV', 222, 'NA'),
(209, 'SY', 'Syria', 'Syrian Arab Republic', 'SYR', 760, 'AS'),
(210, 'SZ', 'Swaziland', 'Kingdom of Swaziland', 'SWZ', 748, 'AF'),
(211, 'TC', 'Turks and Caicos Islands', 'Turks and Caicos Islands', 'TCA', 796, 'NA'),
(212, 'TD', 'Chad', 'Republic of Chad', 'TCD', 148, 'AF'),
(213, 'TF', 'French Southern Lands', 'French Southern Territories', 'ATF', 260, 'AN'),
(214, 'TG', 'Togo', 'Togolese Republic', 'TGO', 768, 'AF'),
(215, 'TH', 'Thailand', 'Kingdom of Thailand', 'THA', 764, 'AS'),
(216, 'TJ', 'Tajikistan', 'Republic of Tajikistan', 'TJK', 762, 'AS'),
(217, 'TK', 'Tokelau', 'Tokelau', 'TKL', 772, 'OC'),
(218, 'TL', 'Timor-Leste', 'Democratic Republic of Timor-Leste', 'TLS', 626, 'AS'),
(219, 'TM', 'Turkmenistan', 'Turkmenistan', 'TKM', 795, 'AS'),
(220, 'TN', 'Tunisia', 'Tunisian Republic', 'TUN', 788, 'AF'),
(221, 'TO', 'Tonga', 'Kingdom of Tonga', 'TON', 776, 'OC'),
(222, 'TR', 'Turkey', 'Republic of Turkey', 'TUR', 792, 'AS'),
(223, 'TT', 'Trinidad and Tobago', 'Republic of Trinidad and Tobago', 'TTO', 780, 'NA'),
(224, 'TV', 'Tuvalu', 'Tuvalu', 'TUV', 798, 'OC'),
(225, 'TW', 'Taiwan', 'Taiwan', 'TWN', 158, 'AS'),
(226, 'TZ', 'Tanzania', 'United Republic of Tanzania', 'TZA', 834, 'AF'),
(227, 'UA', 'Ukraine', 'Ukraine', 'UKR', 804, 'EU'),
(228, 'UG', 'Uganda', 'Republic of Uganda', 'UGA', 800, 'AF'),
(229, 'UM', 'United States Minor Outlying Islands', 'United States Minor Outlying Islands', 'UMI', 581, 'OC'),
(230, 'US', 'United States of America', 'United States of America', 'USA', 840, 'NA'),
(231, 'UY', 'Uruguay', 'Eastern Republic of Uruguay', 'URY', 858, 'SA'),
(232, 'UZ', 'Uzbekistan', 'Republic of Uzbekistan', 'UZB', 860, 'AS'),
(233, 'VA', 'Vatican City', 'Holy See (Vatican City State)', 'VAT', 336, 'EU'),
(234, 'VC', 'Saint Vincent and the Grenadines', 'Saint Vincent and the Grenadines', 'VCT', 670, 'NA'),
(235, 'VE', 'Venezuela', 'Bolivarian Republic of Venezuela', 'VEN', 862, 'SA'),
(236, 'VG', 'Virgin Islands, British', 'British Virgin Islands', 'VGB', 092, 'NA'),
(237, 'VI', 'Virgin Islands, U.S.', 'United States Virgin Islands', 'VIR', 850, 'NA'),
(238, 'VN', 'Vietnam', 'Socialist Republic of Vietnam', 'VNM', 704, 'AS'),
(239, 'VU', 'Vanuatu', 'Republic of Vanuatu', 'VUT', 548, 'OC'),
(240, 'WF', 'Wallis and Futuna Islands', 'Wallis and Futuna', 'WLF', 876, 'OC'),
(241, 'WS', 'Samoa', 'Independent State of Samoa', 'WSM', 882, 'OC'),
(242, 'YE', 'Yemen', 'Yemen', 'YEM', 887, 'AS'),
(243, 'YT', 'Mayotte', 'Mayotte', 'MYT', 175, 'AF'),
(244, 'ZA', 'South Africa', 'Republic of South Africa', 'ZAF', 710, 'AF'),
(245, 'ZM', 'Zambia', 'Republic of Zambia', 'ZMB', 894, 'AF'),
(246, 'ZW', 'Zimbabwe', 'Republic of Zimbabwe', 'ZWE', 716, 'AF');

-- --------------------------------------------------------

--
-- Table structure for table `t_currency`
--

CREATE TABLE IF NOT EXISTS `t_currency` (
  `currency` varchar(3) NOT NULL,
  `currency_name` varchar(30) NOT NULL,
  `date_created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`currency`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `t_currency`
--

INSERT INTO `t_currency` (`currency`, `currency_name`, `date_created`) VALUES
('EUR', 'European Currency', '0000-00-00 00:00:00'),
('INR', 'Indian Currency', '0000-00-00 00:00:00'),
('USD', 'US Currency', '0000-00-00 00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `t_docs`
--

CREATE TABLE IF NOT EXISTS `t_docs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uploader_id` varchar(100) NOT NULL,
  `uploader_type` enum('agent','ops','cardholder','bank') DEFAULT NULL,
  `doc_type` enum('pan','passport','others','photo','shopphoto','idproof','addressproof') NOT NULL,
  `file_name` varchar(100) DEFAULT NULL,
  `file_type` varchar(4) DEFAULT NULL,
  `date_created` datetime NOT NULL,
  `status` varchar(12) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=58 ;

--
-- Dumping data for table `t_docs`
--

INSERT INTO `t_docs` (`id`, `uploader_id`, `uploader_type`, `doc_type`, `file_name`, `file_type`, `date_created`, `status`) VALUES
(1, '41', 'agent', 'passport', '1.jpg', 'jpg', '2012-11-15 16:11:32', 'active'),
(2, '41', 'agent', '', '2.jpg', 'jpg', '2012-11-15 16:13:00', 'active'),
(3, '41', 'agent', '', '3.', NULL, '2012-11-15 16:24:21', 'active'),
(4, '41', 'agent', 'passport', '4.jpg', 'jpg', '2012-11-15 16:40:10', 'active'),
(5, '41', 'agent', 'photo', '5.jpg', 'jpg', '2012-11-15 16:40:56', 'active'),
(6, '41', 'agent', 'photo', '6.jpg', 'jpg', '2012-11-15 16:41:40', 'active'),
(7, '41', 'agent', 'passport', '7.jpg', 'jpg', '2012-11-15 16:43:08', 'active'),
(8, '41', 'agent', 'shopphoto', '8.jpg', 'jpg', '2012-11-15 16:44:20', 'active'),
(9, '41', 'agent', 'shopphoto', '9.jpg', 'jpg', '2012-11-15 16:44:41', 'active'),
(10, '41', 'agent', 'photo', '10.jpg', 'jpg', '2012-11-15 16:49:22', 'active'),
(11, '41', 'agent', 'shopphoto', '11.jpg', 'jpg', '2012-11-15 16:50:25', 'active'),
(12, '41', 'agent', 'passport', '12.jpg', 'jpg', '2012-11-15 16:51:48', 'active'),
(13, '41', 'agent', '', '13.jpg', 'jpg', '2012-11-15 16:52:03', 'active'),
(14, '41', 'agent', '', '14.jpg', 'jpg', '2012-11-15 17:00:11', 'active'),
(15, '41', 'agent', 'photo', '15.jpg', 'jpg', '2012-11-16 19:39:59', 'active'),
(16, '41', 'agent', 'photo', '16.jpg', 'jpg', '2012-11-16 19:47:47', 'active'),
(17, '41', 'agent', 'pan', '17.jpg', 'jpg', '2012-11-16 19:47:47', 'active'),
(18, '41', 'agent', 'pan', '18.jpg', 'jpg', '2012-11-16 19:47:47', 'active'),
(19, '41', 'agent', 'photo', '19.jpg', 'jpg', '2012-11-16 19:48:38', 'active'),
(20, '41', 'agent', 'pan', '20.jpg', 'jpg', '2012-11-16 19:48:39', 'active'),
(21, '41', 'agent', 'photo', '21.jpg', 'jpg', '2012-11-16 19:53:12', 'active'),
(22, '41', 'agent', 'pan', '22.jpg', 'jpg', '2012-11-16 19:53:12', 'active'),
(23, '41', 'agent', 'photo', '23.jpg', 'jpg', '2012-11-16 19:55:47', 'active'),
(24, '41', 'agent', 'pan', '24.jpg', 'jpg', '2012-11-16 19:55:47', 'active'),
(25, '41', 'agent', 'pan', '25.jpg', 'jpg', '2012-11-16 19:58:11', 'active'),
(26, '41', 'agent', 'photo', '26.jpg', 'jpg', '2012-11-16 19:58:46', 'active'),
(27, '41', 'agent', 'pan', '27.jpg', 'jpg', '2012-11-16 19:58:47', 'active'),
(28, '41', 'agent', 'photo', '28.jpg', 'jpg', '2012-11-16 19:59:20', 'active'),
(29, '41', 'agent', 'pan', '29.jpg', 'jpg', '2012-11-16 19:59:20', 'active'),
(30, '41', 'agent', 'photo', '30.jpg', 'jpg', '2012-11-16 20:00:38', 'active'),
(31, '41', 'agent', 'pan', '31.jpg', 'jpg', '2012-11-16 20:00:38', 'active'),
(32, '41', 'agent', 'photo', '32.jpg', 'jpg', '2012-11-16 20:02:29', 'active'),
(33, '41', 'agent', 'shopphoto', '33.jpg', 'jpg', '2012-11-16 20:02:29', 'active'),
(34, '41', 'agent', 'passport', '34.jpg', 'jpg', '2012-11-16 20:03:32', 'active'),
(35, '41', 'agent', 'shopphoto', '35.jpg', 'jpg', '2012-11-16 20:03:33', 'active'),
(36, '41', 'agent', 'passport', '36.jpg', 'jpg', '2012-11-16 20:03:33', 'active'),
(37, '86', 'agent', 'passport', '37.jpg', 'jpg', '2012-11-16 20:03:33', 'active'),
(38, '86', 'agent', 'shopphoto', '38.jpg', 'jpg', '2012-11-16 20:04:38', 'active'),
(39, '41', 'agent', 'shopphoto', '39.jpg', 'jpg', '2012-11-16 20:04:38', 'active'),
(40, '41', 'agent', 'photo', '40.jpg', 'jpg', '2012-11-16 20:05:33', 'active'),
(41, '41', 'agent', 'shopphoto', '41.jpg', 'jpg', '2012-11-16 20:05:33', 'active'),
(42, '41', 'agent', 'idproof', '42.jpg', 'jpg', '2012-11-16 20:05:34', 'active'),
(43, '41', 'agent', 'photo', '43.jpg', 'jpg', '2012-11-16 20:10:11', 'active'),
(44, '41', 'agent', 'shopphoto', '44.jpg', 'jpg', '2012-11-16 20:10:12', 'active'),
(45, '86', 'agent', 'photo', '45.jpg', 'jpg', '2012-11-19 10:54:03', 'active'),
(46, '71', 'agent', 'photo', '46.jpg', 'jpg', '2012-11-19 13:53:49', 'active'),
(47, '71', 'agent', 'idproof', '47.jpg', 'jpg', '2012-11-19 13:53:49', 'active'),
(48, '71', 'agent', 'shopphoto', '48.jpg', 'jpg', '2012-11-19 13:53:49', 'active'),
(49, '80', 'agent', 'photo', '49.jpg', 'jpg', '2012-11-19 15:31:59', 'active'),
(50, '80', 'agent', 'idproof', '50.jpg', 'jpg', '2012-11-19 15:32:00', 'active'),
(51, '80', 'agent', 'shopphoto', '51.jpg', 'jpg', '2012-11-19 15:32:00', 'active'),
(52, '86', 'agent', 'pan', '52.jpg', 'jpg', '2012-11-19 15:34:05', 'active'),
(53, '83', 'agent', 'photo', '53.jpeg', 'jpeg', '2012-11-19 18:15:07', 'active'),
(54, '83', 'agent', 'passport', '54.jpg', 'jpg', '2012-11-19 18:15:07', 'active'),
(55, '83', 'agent', 'shopphoto', '55.jpeg', 'jpeg', '2012-11-19 18:15:07', 'active'),
(56, '69', 'agent', 'passport', '56.jpg', 'jpg', '2012-11-21 15:54:14', 'active'),
(57, '89', 'agent', 'passport', '57.jpg', 'jpg', '2012-11-22 13:06:32', 'active');

-- --------------------------------------------------------

--
-- Table structure for table `t_ecs_crn`
--

CREATE TABLE IF NOT EXISTS `t_ecs_crn` (
  `crn` varchar(30) NOT NULL,
  `status` enum('free','block') DEFAULT 'free',
  `date_created` timestamp NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`crn`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `t_ecs_crn`
--

INSERT INTO `t_ecs_crn` (`crn`, `status`, `date_created`) VALUES
('crn00001', 'block', '2012-11-07 13:20:13'),
('crn00002', 'block', '2012-11-07 13:30:41'),
('crn00003', 'block', '2012-11-07 13:50:53'),
('crn00004', 'block', '2012-11-07 14:29:17'),
('crn00005', 'block', '2012-11-08 05:23:40'),
('crn00006', 'block', '2012-11-08 05:28:04'),
('crn00007', 'block', '2012-11-08 07:06:09'),
('crn00008', 'block', '2012-11-08 07:44:17'),
('crn00009', 'block', '2012-11-08 07:54:02'),
('crn00010', 'block', '2012-11-08 08:43:55'),
('crn000100', 'block', '2012-11-19 07:48:13'),
('crn000101', 'block', '2012-11-19 07:48:18'),
('crn000102', 'block', '2012-11-19 07:48:24'),
('crn000103', 'block', '2012-11-19 08:03:41'),
('crn000104', 'block', '2012-11-19 08:03:46'),
('crn000105', 'block', '2012-11-19 08:03:59'),
('crn000106', 'block', '2012-11-19 08:04:04'),
('crn000107', 'block', '2012-11-19 08:04:17'),
('crn000108', 'block', '2012-11-19 08:04:29'),
('crn000109', 'block', '2012-11-19 08:07:57'),
('crn00011', 'block', '2012-11-08 08:53:20'),
('crn000110', 'block', '2012-11-19 08:08:07'),
('crn000111', 'block', '2012-11-19 08:08:44'),
('crn000112', 'block', '2012-11-19 08:08:55'),
('crn000113', 'block', '2012-11-19 08:09:01'),
('crn000114', 'block', '2012-11-19 08:09:20'),
('crn000115', 'block', '2012-11-19 08:53:06'),
('crn000116', 'block', '2012-11-19 08:53:11'),
('crn000117', 'block', '2012-11-19 08:53:19'),
('crn000118', 'block', '2012-11-19 08:53:24'),
('crn000119', 'block', '2012-11-19 08:53:36'),
('crn00012', 'block', '2012-11-08 10:21:14'),
('crn000120', 'block', '2012-11-19 09:27:10'),
('crn000121', 'block', '2012-11-19 09:27:39'),
('crn000122', 'block', '2012-11-19 09:27:49'),
('crn000123', 'block', '2012-11-19 09:28:02'),
('crn000124', 'block', '2012-11-19 10:15:23'),
('crn000125', 'block', '2012-11-19 10:15:52'),
('crn000126', 'block', '2012-11-19 10:16:01'),
('crn000127', 'block', '2012-11-19 10:16:08'),
('crn000128', 'block', '2012-11-19 10:25:19'),
('crn000129', 'block', '2012-11-19 10:25:29'),
('crn00013', 'block', '2012-11-08 10:52:05'),
('crn000130', 'block', '2012-11-19 10:25:34'),
('crn000131', 'block', '2012-11-19 11:00:46'),
('crn000132', 'block', '2012-11-19 11:00:52'),
('crn000133', 'block', '2012-11-19 11:00:59'),
('crn000134', 'block', '2012-11-19 13:39:43'),
('crn000135', 'block', '2012-11-19 13:40:51'),
('crn000136', 'block', '2012-11-19 13:41:26'),
('crn000137', 'block', '2012-11-19 13:42:37'),
('crn000138', 'block', '2012-11-20 08:47:45'),
('crn000139', 'block', '2012-11-20 08:47:57'),
('crn00014', 'block', '2012-11-08 11:06:47'),
('crn000140', 'block', '2012-11-20 08:48:05'),
('crn000141', 'block', '2012-11-21 07:30:25'),
('crn000142', 'block', '2012-11-21 07:30:49'),
('crn000143', 'block', '2012-11-21 07:31:02'),
('crn000144', 'block', '2012-11-21 09:43:05'),
('crn000145', 'block', '2012-11-21 09:43:18'),
('crn000146', 'block', '2012-11-21 09:43:21'),
('crn000147', 'block', '2012-11-21 09:43:25'),
('crn000148', 'block', '2012-11-21 12:33:07'),
('crn000149', 'block', '2012-11-21 12:33:16'),
('crn00015', 'block', '2012-11-08 11:10:28'),
('crn000150', 'block', '2012-11-21 12:38:14'),
('crn000151', 'block', '2012-11-21 13:01:05'),
('crn00016', 'block', '2012-11-08 11:16:16'),
('crn00017', 'block', '2012-11-08 11:34:26'),
('crn00018', 'block', '2012-11-08 12:28:34'),
('crn00019', 'block', '2012-11-08 14:02:12'),
('crn00020', 'block', '2012-11-08 14:10:04'),
('crn00021', 'block', '2012-11-09 05:23:30'),
('crn00022', 'block', '2012-11-09 05:30:42'),
('crn00023', 'block', '2012-11-09 05:45:10'),
('crn00024', 'block', '2012-11-09 06:20:36'),
('crn00025', 'block', '2012-11-09 06:39:09'),
('crn00026', 'block', '2012-11-09 06:55:53'),
('crn00027', 'block', '2012-11-09 06:58:22'),
('crn00028', 'block', '2012-11-09 10:50:50'),
('crn00029', 'block', '2012-11-09 11:13:25'),
('crn00030', 'block', '2012-11-09 11:24:39'),
('crn00031', 'block', '2012-11-09 12:20:29'),
('crn00032', 'block', '2012-11-09 12:28:31'),
('crn00033', 'block', '2012-11-09 13:23:29'),
('crn00034', 'block', '2012-11-09 13:45:53'),
('crn00035', 'block', '2012-11-12 05:58:05'),
('crn00036', 'block', '2012-11-17 07:12:50'),
('crn00037', 'block', '2012-11-17 07:15:36'),
('crn00038', 'block', '2012-11-17 07:17:01'),
('crn00039', 'block', '2012-11-17 07:17:24'),
('crn00040', 'block', '2012-11-17 07:20:40'),
('crn00041', 'block', '2012-11-17 07:21:00'),
('crn00042', 'block', '2012-11-17 07:22:10'),
('crn00043', 'block', '2012-11-17 07:22:25'),
('crn00044', 'block', '2012-11-17 07:22:25'),
('crn00045', 'block', '2012-11-17 07:23:51'),
('crn00046', 'block', '2012-11-17 07:24:34'),
('crn00047', 'block', '2012-11-17 07:31:01'),
('crn00048', 'block', '2012-11-17 07:33:37'),
('crn00049', 'block', '2012-11-17 07:33:39'),
('crn00050', 'block', '2012-11-17 07:34:02'),
('crn00051', 'block', '2012-11-17 07:34:23'),
('crn00052', 'block', '2012-11-17 07:34:30'),
('crn00053', 'block', '2012-11-17 07:34:40'),
('crn00054', 'block', '2012-11-17 07:35:13'),
('crn00055', 'block', '2012-11-17 07:36:43'),
('crn00056', 'block', '2012-11-17 07:36:55'),
('crn00057', 'block', '2012-11-17 07:37:06'),
('crn00058', 'block', '2012-11-17 07:37:12'),
('crn00059', 'block', '2012-11-17 07:38:22'),
('crn00060', 'block', '2012-11-17 07:40:07'),
('crn00061', 'block', '2012-11-17 07:51:59'),
('crn00062', 'block', '2012-11-17 07:52:19'),
('crn00063', 'block', '2012-11-17 07:52:49'),
('crn00064', 'block', '2012-11-17 07:59:14'),
('crn00065', 'block', '2012-11-17 07:59:55'),
('crn00066', 'block', '2012-11-17 08:00:44'),
('crn00067', 'block', '2012-11-17 08:00:59'),
('crn00068', 'block', '2012-11-17 08:03:10'),
('crn00069', 'block', '2012-11-17 08:03:26'),
('crn00070', 'block', '2012-11-21 13:01:46'),
('crn00071', 'block', '2012-11-21 13:01:58'),
('crn00072', 'block', '2012-11-21 13:02:25'),
('crn00073', 'block', '2012-11-21 13:02:28'),
('crn00074', 'block', '2012-11-21 13:32:22'),
('crn00075', 'block', '2012-11-21 14:01:21'),
('crn00076', 'block', '2012-11-21 14:01:31'),
('crn00077', 'block', '2012-11-21 14:06:52'),
('crn00078', 'block', '2012-11-21 14:06:58'),
('crn00079', 'block', '2012-11-21 14:12:18'),
('crn00080', 'block', '2012-11-21 14:19:29'),
('crn00081', 'block', '2012-11-21 14:19:34'),
('crn00082', 'block', '2012-11-22 06:42:18'),
('crn00083', 'block', '2012-11-22 06:43:44'),
('crn00084', 'block', '2012-11-22 06:43:47'),
('crn00085', 'block', '2012-11-22 06:43:48'),
('crn00086', 'block', '2012-11-22 06:43:50'),
('crn00087', 'block', '2012-11-22 06:45:47'),
('crn00088', 'block', '2012-11-22 06:45:48'),
('crn00089', 'block', '2012-11-22 06:46:37'),
('crn00090', 'block', '2012-11-22 06:48:05'),
('crn00091', 'block', '2012-11-22 06:55:28'),
('crn00092', 'block', '2012-11-22 06:58:53'),
('crn00093', 'block', '2012-11-22 06:59:21'),
('crn00094', 'block', '2012-11-22 07:01:16'),
('crn00095', 'block', '2012-11-22 07:01:31'),
('crn00096', 'block', '2012-11-22 08:46:30'),
('crn00097', 'block', '2012-11-22 08:46:39'),
('crn00098', 'block', '2012-11-22 08:57:09'),
('crn00099', 'block', '2012-11-22 08:57:26'),
('crn00152', 'block', '2012-11-22 09:46:53'),
('crn00153', 'block', '2012-11-22 09:47:04'),
('crn00154', 'block', '2012-11-22 09:47:50'),
('crn00155', 'block', '2012-11-22 10:07:40'),
('crn00156', 'block', '2012-11-22 10:07:44'),
('crn00157', 'block', '2012-11-22 10:07:53'),
('crn00158', 'block', '2012-11-22 10:08:09'),
('crn00159', 'block', '2012-11-22 10:17:00'),
('crn00160', 'block', '2012-11-22 10:17:02'),
('crn00161', 'block', '2012-11-22 10:17:16'),
('crn00162', 'block', '2012-11-22 12:02:43'),
('crn00163', 'block', '2012-11-22 12:02:52'),
('crn00164', 'block', '2012-11-23 05:54:16'),
('crn00165', 'block', '2012-11-23 05:55:03'),
('crn00166', 'block', '2012-11-23 06:57:51'),
('crn00167', 'block', '2012-11-23 06:57:54'),
('crn00168', 'block', '2012-11-23 06:58:00'),
('crn00169', 'block', '2012-11-23 10:01:05'),
('crn00170', 'block', '2012-11-23 10:01:12'),
('crn00171', 'block', '2012-11-23 10:01:17'),
('crn00172', 'free', '0000-00-00 00:00:00'),
('crn00173', 'free', '0000-00-00 00:00:00'),
('crn00174', 'free', '0000-00-00 00:00:00'),
('crn00175', 'free', '0000-00-00 00:00:00'),
('crn00176', 'free', '0000-00-00 00:00:00'),
('crn00177', 'free', '0000-00-00 00:00:00'),
('crn00178', 'free', '0000-00-00 00:00:00'),
('crn00179', 'free', '0000-00-00 00:00:00'),
('crn00180', 'free', '0000-00-00 00:00:00'),
('crn00181', 'free', '0000-00-00 00:00:00'),
('crn00182', 'free', '0000-00-00 00:00:00'),
('crn00183', 'free', '0000-00-00 00:00:00'),
('crn00184', 'free', '0000-00-00 00:00:00'),
('crn00185', 'free', '0000-00-00 00:00:00'),
('crn00186', 'free', '0000-00-00 00:00:00'),
('crn00187', 'free', '0000-00-00 00:00:00'),
('crn00188', 'free', '0000-00-00 00:00:00'),
('crn00189', 'free', '0000-00-00 00:00:00'),
('crn00190', 'free', '0000-00-00 00:00:00'),
('crn00191', 'free', '0000-00-00 00:00:00'),
('crn00192', 'free', '0000-00-00 00:00:00'),
('crn00193', 'free', '0000-00-00 00:00:00'),
('crn00194', 'free', '0000-00-00 00:00:00'),
('crn00195', 'free', '0000-00-00 00:00:00'),
('crn00196', 'free', '0000-00-00 00:00:00'),
('crn00197', 'free', '0000-00-00 00:00:00'),
('crn00198', 'free', '0000-00-00 00:00:00'),
('crn00199', 'free', '0000-00-00 00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `t_fee`
--

CREATE TABLE IF NOT EXISTS `t_fee` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `name` varchar(24) NOT NULL,
  `currency` char(3) NOT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  PRIMARY KEY (`id`),
  KEY `fk_product_id` (`product_id`) USING BTREE
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=13 ;

--
-- Dumping data for table `t_fee`
--

INSERT INTO `t_fee` (`id`, `product_id`, `name`, `currency`, `status`) VALUES
(1, 1, 'General Fee', 'INR', 'active'),
(3, 7, 'Promotional Fee (BOI)', 'INR', 'active'),
(5, 12, 'ICICI Based Fee', 'INR', 'active'),
(6, 13, 'Genric Fee', 'INR', 'active'),
(7, 14, 'YESBANK FEE NEW', 'INR', 'active'),
(8, 15, 'SBI Base Fee', 'INR', 'active'),
(9, 15, 'SBI Future Group', 'INR', 'active'),
(10, 5, 'Axis Bank test fee', 'INR', 'active'),
(11, 15, 'SBI Sahara Group', 'INR', 'active'),
(12, 5, 'New MASTER', 'INR', 'active');

-- --------------------------------------------------------

--
-- Table structure for table `t_fee_info`
--

CREATE TABLE IF NOT EXISTS `t_fee_info` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fee_id` int(11) NOT NULL,
  `currency` char(3) NOT NULL,
  `limit_min` decimal(9,4) NOT NULL,
  `limit_max` decimal(9,4) NOT NULL,
  `limit_first_load` decimal(9,4) NOT NULL,
  `load_limit_min` decimal(9,4) NOT NULL,
  `load_limit_max` decimal(9,4) NOT NULL,
  `date_start` date NOT NULL,
  `date_end` date NOT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'inactive',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=33 ;

--
-- Dumping data for table `t_fee_info`
--

INSERT INTO `t_fee_info` (`id`, `fee_id`, `currency`, `limit_min`, `limit_max`, `limit_first_load`, `load_limit_min`, `load_limit_max`, `date_start`, `date_end`, `status`) VALUES
(1, 1, 'INR', 0.0000, 10000.0000, 1000.0000, 100.0000, 3000.0000, '2012-11-16', '0000-00-00', 'active'),
(2, 2, 'INR', 3000.0000, 10000.0000, 1000.0000, 0.0000, 0.0000, '2012-11-15', '0000-00-00', 'active'),
(7, 4, 'INR', 1.0000, 200.0000, 3.0000, 45.0000, 12.0000, '2012-11-14', '2012-11-16', 'inactive'),
(9, 5, 'INR', 0.0000, 10000.0000, 500.0000, 200.0000, 10000.0000, '2012-11-19', '0000-00-00', 'active'),
(10, 3, 'INR', 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, '2012-11-20', '2012-11-19', 'inactive'),
(11, 3, 'INR', 0.0000, 0.0000, 0.0000, 50.0000, 120.0000, '2012-11-20', '0000-00-00', 'active'),
(13, 7, 'INR', 0.0000, 10000.0000, 1500.0000, 1000.0000, 5000.0000, '2012-11-20', '0000-00-00', 'active'),
(14, 8, 'INR', 100.0000, 10000.0000, 400.0000, 200.0000, 10000.0000, '2012-11-20', '2012-11-19', 'inactive'),
(15, 8, 'INR', 100.0000, 10000.0000, 400.0000, 200.0000, 2500.0000, '2012-11-20', '2012-11-21', 'inactive'),
(16, 8, 'INR', 100.0000, 10000.0000, 400.0000, 200.0000, 4000.0000, '2012-11-22', '2012-11-26', 'inactive'),
(17, 9, 'INR', 100.0000, 10000.0000, 500.0000, 200.0000, 3000.0000, '2012-12-01', '0000-00-00', 'active'),
(18, 10, 'INR', 100.0000, 10000.0000, 200.0000, 100.0000, 10000.0000, '2012-11-21', '0000-00-00', 'active'),
(19, 8, 'INR', 200.0000, 20000.0000, 600.0000, 300.0000, 10000.0000, '2012-11-27', '0000-00-00', 'active'),
(20, 11, 'INR', 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, '2012-11-22', '2012-11-21', 'inactive'),
(21, 11, 'INR', 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, '2012-11-22', '2012-11-22', 'inactive'),
(22, 11, 'INR', 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, '2012-11-23', '2012-11-27', 'inactive'),
(23, 11, 'INR', 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, '2012-11-30', '2012-11-22', 'inactive'),
(25, 11, 'INR', 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, '2012-11-28', '2012-11-27', 'inactive'),
(26, 11, 'INR', 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, '2012-11-28', '2012-12-05', 'inactive'),
(27, 11, 'INR', 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, '2012-12-06', '2012-12-05', 'inactive'),
(28, 11, 'INR', 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, '2012-12-06', '2012-12-07', 'inactive'),
(29, 11, 'INR', 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, '2012-12-08', '2012-12-09', 'inactive'),
(30, 6, 'INR', 2.0000, 34.0000, 0.0000, 0.0000, 211.0000, '2012-11-23', '2012-11-23', 'inactive'),
(31, 6, 'INR', 2.0000, 34.0000, 0.0000, 0.0000, 211.0000, '2012-11-24', '0000-00-00', 'active'),
(32, 11, 'INR', 0.0000, 20.0000, 0.0000, 0.0000, 0.0000, '2012-12-10', '0000-00-00', 'active');

-- --------------------------------------------------------

--
-- Table structure for table `t_fee_items`
--

CREATE TABLE IF NOT EXISTS `t_fee_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fee_id` int(11) NOT NULL,
  `typecode_name` varchar(50) NOT NULL,
  `typecode` char(4) NOT NULL,
  `txn_flat` decimal(9,4) NOT NULL,
  `txn_pcnt` decimal(5,2) NOT NULL,
  `txn_min` decimal(9,4) NOT NULL,
  `txn_max` decimal(9,4) NOT NULL,
  `date_start` date NOT NULL,
  `date_end` date NOT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  PRIMARY KEY (`id`),
  KEY `fk_fee_id` (`fee_id`) USING BTREE
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=29 ;

--
-- Dumping data for table `t_fee_items`
--

INSERT INTO `t_fee_items` (`id`, `fee_id`, `typecode_name`, `typecode`, `txn_flat`, `txn_pcnt`, `txn_min`, `txn_max`, `date_start`, `date_end`, `status`) VALUES
(1, 1, 'Card Load', 'CDLD', 100.0000, 0.00, 0.0000, 0.0000, '2012-11-16', '0000-00-00', 'active'),
(3, 1, '', 'CRAT', 0.0000, 0.00, 0.0000, 0.0000, '2012-11-16', '2012-11-19', 'inactive'),
(4, 1, 'Cardholder Registration', 'CDRG', 2.0000, 0.00, 1.0000, 12.0000, '2012-11-22', '2012-11-21', 'inactive'),
(5, 5, 'Card Load', 'CDLD', 0.0000, 20.00, 50.0000, 200.0000, '2012-11-19', '0000-00-00', 'active'),
(6, 1, 'Cardholder Activation', 'CRAT', 1.0000, 0.00, 0.0000, 0.0000, '2012-11-20', '2012-11-19', 'inactive'),
(7, 1, 'Cardholder Activation', 'CRAT', 0.0000, 10.00, 0.0000, 0.0000, '2012-11-20', '0000-00-00', 'active'),
(8, 1, 'Cardholder Registration', 'CDRG', 2.0000, 0.00, 1.0000, 12.0000, '2012-11-22', '0000-00-00', 'active'),
(9, 1, 'Cardholder Account Reload', 'CDRL', 0.0000, 0.00, 0.0000, 0.0000, '2012-11-13', '2012-11-19', 'inactive'),
(10, 1, 'Cardholder Account Reload', 'CDRL', 0.0000, 0.00, 0.0000, 0.0000, '2012-11-20', '0000-00-00', 'active'),
(11, 3, 'Cardholder Account Reload', 'CDRL', 20.0000, 0.00, 0.0000, 0.0000, '2012-11-21', '0000-00-00', 'active'),
(12, 6, 'Card Load', 'CDLD', 50.0000, 0.00, 0.0000, 0.0000, '2012-11-20', '0000-00-00', 'active'),
(13, 6, 'Cardholder Account Reload', 'CDRL', 0.0000, 5.00, 100.0000, 200.0000, '2012-11-28', '0000-00-00', 'active'),
(14, 6, 'Cardholder Activation', 'CRAT', 0.0000, 10.00, 50.0000, 500.0000, '2012-11-30', '2012-11-27', 'inactive'),
(15, 6, 'Cardholder Activation', 'CRAT', 0.0000, 10.00, 50.0000, 500.0000, '2012-11-28', '0000-00-00', 'active'),
(16, 7, 'Card Load', 'CDLD', 0.0000, 5.00, 100.0000, 1000.0000, '2012-11-20', '0000-00-00', 'active'),
(17, 7, 'Cardholder Registration', 'CDRG', 300.0000, 0.00, 0.0000, 0.0000, '2012-11-20', '0000-00-00', 'active'),
(18, 8, 'Card Load', 'CDLD', 0.0000, 0.40, 50.0000, 100.0000, '2012-11-20', '2012-11-30', 'inactive'),
(19, 8, 'Cardholder Registration', 'CDRG', 10.0000, 0.00, 0.0000, 0.0000, '2012-11-20', '0000-00-00', 'active'),
(20, 8, 'Card Load', 'CDLD', 0.0000, 0.40, 50.0000, 200.0000, '2012-12-01', '0000-00-00', 'active'),
(21, 9, 'Card Load', 'CDLD', 50.0000, 0.00, 0.0000, 0.0000, '2012-12-01', '0000-00-00', 'active'),
(22, 9, 'Cardholder Registration', 'CDRG', 100.0000, 0.00, 0.0000, 0.0000, '2012-12-01', '0000-00-00', 'active'),
(23, 11, 'Cardholder Activation', 'CRAT', 20.0000, 0.00, 0.0000, 0.0000, '2012-11-24', '2012-11-28', 'inactive'),
(24, 11, 'Cardholder Activation', 'CRAT', 0.0000, 10.00, 20.0000, 100.0000, '2012-11-29', '2012-12-07', 'inactive'),
(26, 11, 'Cardholder Activation', 'CRAT', 0.0000, 10.00, 20.0000, 100.0000, '2012-12-08', '0000-00-00', 'active'),
(27, 11, 'Cardholder Account Reload', 'CDRL', 0.0000, 23.00, 12.0000, 26.0000, '2012-11-26', '2012-11-23', 'inactive'),
(28, 11, 'Cardholder Account Reload', 'CDRL', 0.0000, 23.00, 12.0000, 26.0000, '2012-11-24', '0000-00-00', 'active');

-- --------------------------------------------------------

--
-- Table structure for table `t_flags`
--

CREATE TABLE IF NOT EXISTS `t_flags` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `description` varchar(255) NOT NULL,
  `active_on_dev` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `active_on_prod` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_name1` (`name`) USING BTREE,
  KEY `idx_name1` (`name`) USING BTREE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=31 ;

--
-- Dumping data for table `t_flags`
--

INSERT INTO `t_flags` (`id`, `name`, `description`, `active_on_dev`, `active_on_prod`) VALUES
(1, 'operation-flags', 'Allows user to manage the flags', 1, 1),
(2, 'operation-groups', 'Allows user to manage the user groups', 1, 0),
(3, 'operation-index', 'Default entry point in the application', 1, 0),
(4, 'operation-privileges', 'Allows the users to perform CRUD operations on privileges', 1, 0),
(5, 'operation-profile', 'Allows user to manage their profile data', 1, 0),
(6, 'operation-system', 'Allow the admins to manage critical info, users, groups, permissions, etc.', 1, 0),
(7, 'operation-users', 'Allows the users to perform CRUD operations on other users', 1, 0),
(8, 'agent-index', 'Default entry point in the application', 1, 0),
(9, 'operation-testing', 'Some testing permissions', 1, 0),
(10, 'agent-testing', 'Some testing permissions', 1, 0),
(11, 'agent-profile', 'Allow user to perform CRUD operation on privileges', 1, 0),
(12, 'agent-reports', 'Reports for agents', 1, 0),
(13, 'operation-agentsummary', 'Agent Summary', 1, 0),
(14, 'operation-product', 'product Listing', 1, 0),
(15, 'operation-agentfee', 'agent fee management', 1, 0),
(16, 'operation-ajax', 'Ajax releted stuff', 1, 0),
(17, 'agent-ajax', 'Ajax releted stuff', 1, 0),
(18, 'operation-approveagent', 'Approving Agents', 1, 0),
(19, 'operation-agentlimit', 'operation loads  agent limit', 1, 0),
(20, 'agent-cardholder', 'Cardholder releated stuff', 1, 0),
(21, 'agent-signup', 'Agent Signuup', 1, 0),
(22, 'operation-approvedagent', 'Edit Approved agents list, assign different products or agent  fee.', 1, 0),
(23, 'operation-cardholder', 'All Card holders listing on Operation portal', 1, 0),
(24, 'operation-agentsignup', 'Agent signup process in steps', 1, 0),
(25, 'agent-emailauthorization', 'Agent Email authorization for his approval', 1, 0),
(26, 'operation-bank', 'Add, edit, delete for Banks', 1, 0),
(27, 'operation-filedownload', 'for showing file download link for Agent docs', 1, 0),
(28, 'agent-loadbalance', 'loading balance to cardholder', 1, 0),
(29, 'agent-agentsignup', 'Agent signup phone verification step', 1, 0),
(30, 'operation-transaction', 'Transaction listing and additon in Operation portal', 1, 0);

-- --------------------------------------------------------

--
-- Table structure for table `t_flippers`
--

CREATE TABLE IF NOT EXISTS `t_flippers` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `group_id` int(11) unsigned NOT NULL,
  `flag_id` int(11) unsigned NOT NULL,
  `privilege_id` int(11) unsigned NOT NULL,
  `allow` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_group_id` (`group_id`) USING BTREE,
  KEY `idx_flag_id` (`flag_id`) USING BTREE,
  KEY `idx_privilege_id` (`privilege_id`) USING BTREE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=184 ;

--
-- Dumping data for table `t_flippers`
--

INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES
(34, 3, 8, 26, 1),
(35, 3, 8, 27, 1),
(36, 3, 8, 30, 1),
(37, 3, 11, 31, 1),
(38, 3, 11, 32, 1),
(39, 3, 2, 4, 1),
(40, 3, 2, 5, 1),
(41, 3, 2, 6, 1),
(42, 3, 2, 7, 1),
(43, 3, 2, 8, 1),
(44, 3, 5, 14, 1),
(45, 3, 5, 15, 1),
(46, 3, 5, 16, 1),
(47, 3, 5, 17, 1),
(48, 3, 5, 18, 1),
(49, 3, 6, 19, 1),
(50, 3, 6, 20, 1),
(163, 2, 17, 46, 1),
(164, 2, 25, 38, 1),
(165, 2, 8, 26, 1),
(166, 2, 8, 27, 1),
(167, 2, 8, 30, 1),
(168, 2, 11, 31, 1),
(169, 2, 11, 32, 1),
(170, 2, 11, 33, 1),
(171, 2, 11, 34, 1),
(172, 2, 11, 49, 1),
(173, 2, 21, 35, 1),
(174, 2, 21, 36, 1),
(175, 2, 21, 39, 1),
(176, 2, 21, 40, 1),
(177, 2, 21, 41, 1),
(178, 2, 21, 42, 1),
(179, 2, 21, 43, 1),
(180, 2, 21, 44, 1),
(181, 2, 21, 45, 1),
(182, 2, 16, 47, 1),
(183, 2, 5, 48, 1);

-- --------------------------------------------------------

--
-- Table structure for table `t_groups`
--

CREATE TABLE IF NOT EXISTS `t_groups` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(25) DEFAULT NULL,
  `parent_id` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_name` (`name`) USING BTREE,
  KEY `idx_parent_id` (`parent_id`) USING BTREE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=5 ;

--
-- Dumping data for table `t_groups`
--

INSERT INTO `t_groups` (`id`, `name`, `parent_id`) VALUES
(1, 'administrators', 0),
(2, 'guests', 0),
(3, 'members', 0),
(4, 'Test User', 3);

-- --------------------------------------------------------

--
-- Table structure for table `t_login_log`
--

CREATE TABLE IF NOT EXISTS `t_login_log` (
  `cardholder_id` int(10) unsigned DEFAULT NULL,
  `agent_id` int(10) unsigned DEFAULT NULL,
  `ops_id` int(10) unsigned DEFAULT NULL,
  `bank_id` int(10) unsigned DEFAULT NULL,
  `ip` varchar(15) DEFAULT NULL,
  `login_step1_datetime` datetime DEFAULT NULL,
  `login_step2_datetime` datetime DEFAULT NULL,
  `logout_datetime` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `t_operation_users`
--

CREATE TABLE IF NOT EXISTS `t_operation_users` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `firstname` varchar(20) NOT NULL,
  `lastname` varchar(40) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(40) NOT NULL,
  `password_valid` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `email` varchar(340) NOT NULL,
  `mobile1` varchar(20) DEFAULT NULL,
  `last_login` timestamp NULL DEFAULT NULL,
  `last_password_update` timestamp NULL DEFAULT NULL,
  `auth_code` varchar(20) NOT NULL,
  `num_login_attempts` tinyint(4) NOT NULL DEFAULT '0',
  `status` enum('active','inactive','locked') NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_username` (`username`),
  KEY `idx_email` (`email`(255)) USING BTREE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=12 ;

--
-- Dumping data for table `t_operation_users`
--

INSERT INTO `t_operation_users` (`id`, `firstname`, `lastname`, `username`, `password`, `password_valid`, `email`, `mobile1`, `last_login`, `last_password_update`, `auth_code`, `num_login_attempts`, `status`) VALUES
(1, 'Admin', '', 'vikram', 'bf0ecf4915c10e24cc372612a9604937e4ee55ce', 0, 'vikram@transerv.co.in', '9711198518', '2012-10-29 07:58:22', NULL, '174397', 0, 'active'),
(2, 'test', 'test', 'test', '633f459e809c068a704c0a1189462b7c1bae0106', 0, 'vikram0207@gmail.com', '9810780690', NULL, '2012-10-29 10:24:31', '', 0, 'active'),
(3, 'Ashish ', 'Vats', 'ashish', 'bf0ecf4915c10e24cc372612a9604937e4ee55ce', 0, 'ashish@transerv.co.in', '9810780690', NULL, NULL, '861727', 0, 'active'),
(4, 'Jit Varish', 'Tiwari', 'jit', 'd58c03452e1527b7b1f2ce08e8ad3a8dae9e4774', 0, 'jit@transerv.co.in', '9810780690', '2012-11-20 06:16:56', NULL, '', 0, 'active'),
(5, 'Komal', 'Puri', 'komal', 'bf0ecf4915c10e24cc372612a9604937e4ee55ce', 1, 'komal@transerv.co.in', '9810780690', '2012-11-19 07:12:40', '2012-11-14 10:02:39', '669704', 0, 'active'),
(8, 'Mini', '', 'mini', 'bf0ecf4915c10e24cc372612a9604937e4ee55ce', 0, 'mini@transerv.co.in', '9810780690', NULL, NULL, '', 0, 'active'),
(9, 'Anish', 'Willams', 'anish', 'd58c03452e1527b7b1f2ce08e8ad3a8dae9e4774', 0, 'anish@transerv.co.in', '9920799880', NULL, NULL, '', 0, 'active'),
(11, 'Aniket', 'Labde', 'aniket', 'd58c03452e1527b7b1f2ce08e8ad3a8dae9e4774', 0, 'aniket@transerv.co.in', '9594781803', NULL, NULL, '', 0, 'active');

-- --------------------------------------------------------

--
-- Table structure for table `t_operation_users_groups`
--

CREATE TABLE IF NOT EXISTS `t_operation_users_groups` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `group_id` int(11) unsigned NOT NULL,
  `user_id` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_user_group` (`group_id`,`user_id`) USING BTREE,
  KEY `idx_user_id` (`user_id`) USING BTREE,
  KEY `idx_group_id` (`group_id`) USING BTREE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=11 ;

--
-- Dumping data for table `t_operation_users_groups`
--

INSERT INTO `t_operation_users_groups` (`id`, `group_id`, `user_id`) VALUES
(1, 1, 1),
(2, 1, 3),
(4, 1, 4),
(5, 1, 5),
(8, 1, 8),
(9, 1, 9),
(10, 1, 11),
(7, 2, 2);

-- --------------------------------------------------------

--
-- Table structure for table `t_privileges`
--

CREATE TABLE IF NOT EXISTS `t_privileges` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `flag_id` varchar(20) NOT NULL,
  `description` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_name_flag_id` (`name`,`flag_id`) USING BTREE,
  KEY `idx_resource_id` (`flag_id`) USING BTREE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=50 ;

--
-- Dumping data for table `t_privileges`
--

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES
(1, 'index', '1', 'Allows the user to view all the flags registered in the application'),
(2, 'toggleprod', '1', 'Change the active status of a flag on production'),
(3, 'toggledev', '1', 'Change the active status of a flag on development'),
(4, 'index', '2', 'Allows the user to view all the user groups registered\nin the application'),
(5, 'add', '2', 'Allows the user to add another user group in the\napplication'),
(6, 'edit', '2', 'Edits an existing user group'),
(7, 'delete', '2', 'Allows the user to delete an existing user group. All the users attached to\nthis group *WILL NOT* be deleted, they will just lose all'),
(8, 'flippers', '2', 'Allows the user to manage individual permissions for each\nuser group'),
(9, 'index', '3', 'Controller''s entry point'),
(10, 'index', '4', 'Allows the user to view all the permissions registered\nin the application'),
(11, 'add', '4', 'Allows the user to add another privilege in the application'),
(12, 'edit', '4', 'Edits an existing privilege'),
(13, 'delete', '4', 'Allows the user to delete an existing privilege. All the flippers related to\nthis privilege will be removed'),
(14, 'index', '5', 'Allows users to see their dashboards'),
(15, 'edit', '5', 'Allows the users to update their profiles'),
(16, 'change-password', '5', 'Allows users to change their passwords'),
(17, 'login', '5', 'Allows users to log into the application'),
(18, 'logout', '5', 'Allows users to log out of the application'),
(19, 'index', '6', 'Controller''s entry point'),
(20, 'example', '6', 'Theme example page'),
(21, 'index', '7', 'Allows users to see all other users that are registered in\nthe application'),
(22, 'add', '7', 'Allows users to add new users in the application\n(should be reserved for administrators)'),
(23, 'edit', '7', 'Allows users to edit another users'' data\n(should be reserved for administrators)'),
(24, 'view', '7', 'Allows users to see other users'' profiles'),
(25, 'delete', '7', 'Allows users to logically delete other users\n(should be reserved for administrators)'),
(26, 'index', '8', 'Controller''s entry point'),
(27, 'static', '8', 'Static Pages'),
(28, 'zfdebug', '9', 'Debug toolbar'),
(29, 'zfdebug', '10', 'Debug toolbar'),
(30, 'test', '8', 'test'),
(31, 'index', '11', 'profile landing page'),
(32, 'login', '11', 'Agent Login'),
(33, 'logout', '11', 'Agent Logout'),
(34, 'authcode', '11', 'aa'),
(35, 'index', '21', 'Agent Signup'),
(36, 'process', '21', 'Agent Signup Process'),
(38, 'index', '25', 'Email Authorization - Allowing Guest to activate user'),
(39, 'verification', '21', 'Agent signup phone verification'),
(40, 'add', '21', 'Add Basic details for Agent'),
(41, 'addeducation', '21', 'Add Education details for Agent'),
(42, 'addidentification', '21', 'Add Identification details for Agent'),
(43, 'addaddress', '21', 'Add Address details for agent'),
(44, 'addbank', '21', 'Add bank Details for Agent'),
(45, 'detailscomplete', '21', 'Details complete action where all session variables are unset'),
(46, 'resend-authcode', '17', 'Resend Authcode on Agent portal'),
(47, 'resend-authcode', '16', 'Resend Authcode on Operatipon portal'),
(48, 'resend-authcode', '5', 'Resend Authcode on Operatipon portal'),
(49, 'resend-authcode', '11', 'Resend Authcode on Operatipon portal');

-- --------------------------------------------------------

--
-- Table structure for table `t_products`
--

CREATE TABLE IF NOT EXISTS `t_products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `bank_id` int(11) NOT NULL,
  `name` varchar(60) NOT NULL,
  `description` varchar(100) NOT NULL,
  `currency` char(3) NOT NULL DEFAULT 'INR',
  `ecs_product_code` varchar(10) NOT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  PRIMARY KEY (`id`),
  KEY `fk_bank_idp` (`bank_id`) USING BTREE
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=16 ;

--
-- Dumping data for table `t_products`
--

INSERT INTO `t_products` (`id`, `bank_id`, `name`, `description`, `currency`, `ecs_product_code`, `status`) VALUES
(1, 1, 'MVC Axis Bank Gift Card', 'MVC Axis Bank Gift Card', 'INR', 'ABC12344', 'active'),
(3, 1, 'MVC Axis Bank Test Card1', 'MVC Axis Bank Test Card1', 'INR', 'ABC1234', 'active'),
(4, 1, 'MVC Axis Bank Test Card 2', 'MVC Axis Bank Test Card 2', 'INR', 'ABC1234', 'active'),
(5, 1, 'MVC Axis Bank - test 4', 'sdfasdfasfasdf', 'INR', 'ABC1234', 'active'),
(6, 1, 'New Product test', 'New Product MVC Axis bank', 'INR', 'ABC1234', 'active'),
(7, 2, 'New Product TCS BOI', 'TCS BOI product', 'INR', 'ABC1234', 'active'),
(8, 1, 'Test Product', 'Test product', 'INR', 'ABC1234', 'active'),
(9, 1, 'MVC Test', '43fer`fff', 'INR', 'ABC1234', 'active'),
(11, 2, 'Test  Product TCS BOI', 'Test  Product TCS BOI', 'INR', 'AB1234', 'active'),
(12, 3, 'ICICI New Product', 'ICICI Bank based new Product', 'INR', 'PR05', 'active'),
(13, 5, 'HSBC-MVC', 'HSBC MVC PrePaid Card Program', 'INR', 'ECS HSBC M', 'active'),
(14, 6, 'YESProduct', 'Yes bank & MVC based product', 'INR', 'PR06', 'active'),
(15, 7, 'MVC SBI', 'MVC SBI Prepaid Program', 'INR', 'ECS_9870', 'active');

-- --------------------------------------------------------

--
-- Table structure for table `t_product_fee`
--

CREATE TABLE IF NOT EXISTS `t_product_fee` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `name` varchar(24) NOT NULL,
  `currency` char(3) NOT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  PRIMARY KEY (`id`),
  KEY `fk_product_id` (`product_id`) USING BTREE
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=10 ;

--
-- Dumping data for table `t_product_fee`
--

INSERT INTO `t_product_fee` (`id`, `product_id`, `name`, `currency`, `status`) VALUES
(1, 1, 'General Fee', 'INR', 'active'),
(3, 7, 'Promotional Fee (BOI)', 'INR', 'active'),
(5, 12, 'ICICI Based Fee', 'INR', 'active'),
(6, 13, 'Genric Fee', 'INR', 'active'),
(7, 14, 'YESBANK FEE NEW', 'INR', 'active'),
(8, 15, 'SBI Base Fee', 'INR', 'active'),
(9, 15, 'SBI Future Group', 'INR', 'active');

-- --------------------------------------------------------

--
-- Table structure for table `t_product_fee_items`
--

CREATE TABLE IF NOT EXISTS `t_product_fee_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_fee_id` int(11) NOT NULL,
  `typecode_name` varchar(50) NOT NULL,
  `typecode` char(4) NOT NULL,
  `txn_flat` decimal(9,4) NOT NULL,
  `txn_pcnt` decimal(5,2) NOT NULL,
  `txn_min` decimal(9,4) NOT NULL,
  `txn_max` decimal(9,4) NOT NULL,
  `date_start` date NOT NULL,
  `date_end` date NOT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  PRIMARY KEY (`id`),
  KEY `fk_fee_id` (`product_fee_id`) USING BTREE
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=23 ;

--
-- Dumping data for table `t_product_fee_items`
--

INSERT INTO `t_product_fee_items` (`id`, `product_fee_id`, `typecode_name`, `typecode`, `txn_flat`, `txn_pcnt`, `txn_min`, `txn_max`, `date_start`, `date_end`, `status`) VALUES
(1, 1, 'Card Load', 'CDLD', 100.0000, 0.00, 0.0000, 0.0000, '2012-11-16', '0000-00-00', 'active'),
(3, 1, '', 'CRAT', 0.0000, 0.00, 0.0000, 0.0000, '2012-11-16', '2012-11-19', 'inactive'),
(4, 1, 'Cardholder Registration', 'CDRG', 2.0000, 0.00, 1.0000, 12.0000, '2012-11-22', '2012-11-21', 'inactive'),
(5, 5, 'Card Load', 'CDLD', 0.0000, 20.00, 50.0000, 200.0000, '2012-11-19', '0000-00-00', 'active'),
(6, 1, 'Cardholder Activation', 'CRAT', 1.0000, 0.00, 0.0000, 0.0000, '2012-11-20', '2012-11-19', 'inactive'),
(7, 1, 'Cardholder Activation', 'CRAT', 0.0000, 10.00, 0.0000, 0.0000, '2012-11-20', '0000-00-00', 'active'),
(8, 1, 'Cardholder Registration', 'CDRG', 2.0000, 0.00, 1.0000, 12.0000, '2012-11-22', '0000-00-00', 'active'),
(9, 1, 'Cardholder Account Reload', 'CDRL', 0.0000, 0.00, 0.0000, 0.0000, '2012-11-13', '2012-11-19', 'inactive'),
(10, 1, 'Cardholder Account Reload', 'CDRL', 0.0000, 0.00, 0.0000, 0.0000, '2012-11-20', '0000-00-00', 'active'),
(11, 3, 'Cardholder Account Reload', 'CDRL', 20.0000, 0.00, 0.0000, 0.0000, '2012-11-21', '0000-00-00', 'active'),
(12, 6, 'Card Load', 'CDLD', 50.0000, 0.00, 0.0000, 0.0000, '2012-11-20', '0000-00-00', 'active'),
(13, 6, 'Cardholder Account Reload', 'CDRL', 0.0000, 5.00, 100.0000, 200.0000, '2012-11-28', '0000-00-00', 'active'),
(14, 6, 'Cardholder Activation', 'CRAT', 0.0000, 10.00, 50.0000, 500.0000, '2012-11-30', '2012-11-27', 'inactive'),
(15, 6, 'Cardholder Activation', 'CRAT', 0.0000, 10.00, 50.0000, 500.0000, '2012-11-28', '0000-00-00', 'active'),
(16, 7, 'Card Load', 'CDLD', 0.0000, 5.00, 100.0000, 1000.0000, '2012-11-20', '0000-00-00', 'active'),
(17, 7, 'Cardholder Registration', 'CDRG', 300.0000, 0.00, 0.0000, 0.0000, '2012-11-20', '0000-00-00', 'active'),
(18, 8, 'Card Load', 'CDLD', 0.0000, 0.40, 50.0000, 100.0000, '2012-11-20', '2012-11-30', 'inactive'),
(19, 8, 'Cardholder Registration', 'CDRG', 10.0000, 0.00, 0.0000, 0.0000, '2012-11-20', '0000-00-00', 'active'),
(20, 8, 'Card Load', 'CDLD', 0.0000, 0.40, 50.0000, 200.0000, '2012-12-01', '0000-00-00', 'active'),
(21, 9, 'Card Load', 'CDLD', 50.0000, 0.00, 0.0000, 0.0000, '2012-12-01', '0000-00-00', 'active'),
(22, 9, 'Cardholder Registration', 'CDRG', 100.0000, 0.00, 0.0000, 0.0000, '2012-12-01', '0000-00-00', 'active');

-- --------------------------------------------------------

--
-- Table structure for table `t_product_fee_limit`
--

CREATE TABLE IF NOT EXISTS `t_product_fee_limit` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_fee_id` int(11) NOT NULL,
  `currency` char(3) NOT NULL,
  `limit_out_max_daily` decimal(9,4) NOT NULL,
  `limit_out_max_monthly` decimal(9,4) NOT NULL,
  `limit_out_max_yearly` decimal(9,4) NOT NULL,
  `limit_out_first_load` decimal(9,4) NOT NULL,
  `limit_out_min_txn` decimal(9,4) NOT NULL,
  `limit_out_max_txn` decimal(9,4) NOT NULL,
  `date_start` date NOT NULL,
  `date_end` date NOT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'inactive',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=18 ;

--
-- Dumping data for table `t_product_fee_limit`
--

INSERT INTO `t_product_fee_limit` (`id`, `product_fee_id`, `currency`, `limit_out_max_daily`, `limit_out_max_monthly`, `limit_out_max_yearly`, `limit_out_first_load`, `limit_out_min_txn`, `limit_out_max_txn`, `date_start`, `date_end`, `status`) VALUES
(1, 1, 'INR', 10000.0000, 0.0000, 0.0000, 1000.0000, 100.0000, 3000.0000, '2012-11-16', '0000-00-00', 'active'),
(2, 2, 'INR', 10000.0000, 0.0000, 0.0000, 1000.0000, 0.0000, 0.0000, '2012-11-15', '0000-00-00', 'active'),
(7, 4, 'INR', 200.0000, 0.0000, 0.0000, 3.0000, 45.0000, 12.0000, '2012-11-14', '2012-11-16', 'inactive'),
(9, 5, 'INR', 10000.0000, 0.0000, 0.0000, 500.0000, 200.0000, 10000.0000, '2012-11-19', '0000-00-00', 'active'),
(10, 3, 'INR', 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, '2012-11-20', '2012-11-19', 'inactive'),
(11, 3, 'INR', 0.0000, 0.0000, 0.0000, 0.0000, 50.0000, 120.0000, '2012-11-20', '0000-00-00', 'active'),
(12, 6, 'INR', 2000.0000, 0.0000, 0.0000, 400.0000, 100.0000, 4000.0000, '2012-11-20', '0000-00-00', 'active'),
(13, 7, 'INR', 10000.0000, 0.0000, 0.0000, 1500.0000, 1000.0000, 5000.0000, '2012-11-20', '0000-00-00', 'active'),
(14, 8, 'INR', 10000.0000, 0.0000, 0.0000, 400.0000, 200.0000, 10000.0000, '2012-11-20', '2012-11-19', 'inactive'),
(15, 8, 'INR', 10000.0000, 0.0000, 0.0000, 400.0000, 200.0000, 2500.0000, '2012-11-20', '2012-11-21', 'inactive'),
(16, 8, 'INR', 10000.0000, 0.0000, 0.0000, 400.0000, 200.0000, 4000.0000, '2012-11-22', '0000-00-00', 'active'),
(17, 9, 'INR', 10000.0000, 0.0000, 0.0000, 500.0000, 200.0000, 3000.0000, '2012-12-01', '0000-00-00', 'active');

-- --------------------------------------------------------

--
-- Table structure for table `t_transaction_type`
--

CREATE TABLE IF NOT EXISTS `t_transaction_type` (
  `typecode` char(4) NOT NULL,
  `name` varchar(100) NOT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `date_created` datetime NOT NULL,
  PRIMARY KEY (`typecode`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `t_transaction_type`
--

INSERT INTO `t_transaction_type` (`typecode`, `name`, `status`, `date_created`) VALUES
('CDLD', 'Card Load', 'active', '0000-00-00 00:00:00'),
('CDRG', 'Cardholder Registration', 'active', '0000-00-00 00:00:00'),
('CDRL', 'Cardholder Account Reload', 'active', '0000-00-00 00:00:00'),
('CDUL', 'Card UnLoad', 'active', '2012-11-21 13:53:47'),
('CRAT', 'Cardholder Activation', 'active', '0000-00-00 00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `t_update_log`
--

CREATE TABLE IF NOT EXISTS `t_update_log` (
  `user_id` int(11) NOT NULL,
  `user_type` enum('agent','cardholder','ops') NOT NULL,
  `update_by` enum('agent','ops') NOT NULL,
  `update_id` int(11) NOT NULL,
  `action_taken` varchar(50) NOT NULL,
  `remarks` text NOT NULL,
  `date_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `t_update_log`
--

INSERT INTO `t_update_log` (`user_id`, `user_type`, `update_by`, `update_id`, `action_taken`, `remarks`, `date_modified`) VALUES
(108, 'cardholder', 'ops', 5, 'blocked', 'Yes cardholder needs to be cancelled', '2012-11-09 11:53:04'),
(108, 'cardholder', 'ops', 5, 'blocked', 'Yes cardholder needs to be cancelled', '2012-11-09 11:55:58'),
(108, 'cardholder', 'ops', 5, 'blocked', 'Yes cardholder needs to be cancelled', '2012-11-09 11:56:04'),
(108, 'cardholder', 'ops', 5, 'blocked', 'Yes cardholder needs to be cancelled', '2012-11-09 11:56:36'),
(107, 'cardholder', 'ops', 5, 'blocked', 'cancelled', '2012-11-09 11:57:35'),
(107, 'cardholder', 'ops', 5, 'blocked', 'cancelled', '2012-11-09 11:57:55'),
(107, 'cardholder', 'ops', 5, 'blocked', 'cancelled', '2012-11-09 11:58:25'),
(105, 'cardholder', 'ops', 5, 'blocked', 'deactived', '2012-11-09 11:58:55'),
(105, 'cardholder', 'ops', 5, 'blocked', 'deactived', '2012-11-09 11:59:10'),
(110, 'cardholder', 'ops', 5, 'unblocked', 'activate', '2012-11-09 12:49:47'),
(50, 'agent', 'ops', 5, 'active', 'good profile', '2012-11-09 13:52:45'),
(113, 'cardholder', 'ops', 5, 'unblocked', 'Details verified', '2012-11-12 11:15:47'),
(45, 'agent', 'ops', 5, 'removed', 'details incorrect', '2012-11-12 11:32:51'),
(53, 'agent', 'ops', 5, 'approved', 'Credentials are fine', '2012-11-15 13:53:33'),
(61, 'agent', 'ops', 5, 'approved', 'details found correct', '2012-11-16 09:33:18'),
(53, 'agent', 'ops', 5, 'approved', 'Approved', '2012-11-16 11:21:56'),
(127, 'cardholder', 'ops', 5, 'blocked', 'deactivate', '2012-11-16 13:49:24'),
(120, 'cardholder', 'ops', 5, 'unblocked', 'activated', '2012-11-16 13:49:48'),
(113, 'cardholder', 'ops', 5, 'unblocked', 'activated', '2012-11-16 13:52:56'),
(113, 'cardholder', 'ops', 5, 'blocked', 'Deactivate', '2012-11-16 13:55:09'),
(71, 'agent', 'ops', 9, 'approved', 'sadfasdfasdf\r\nasdfasdfas\r\ndf\r\nasdfasdfasdf', '2012-11-19 07:28:46'),
(71, 'agent', 'ops', 5, 'approved', 'test123', '2012-11-19 07:41:46'),
(71, 'agent', 'ops', 9, 'approved', 'asdfasdf', '2012-11-19 08:37:41'),
(80, 'agent', 'ops', 10, 'approved', 'asdfadsfsdfadsfasdfsadfasdfasdfasdfsdfsdfasdfsadfsadfsdf', '2012-11-19 10:03:43'),
(44, 'agent', 'ops', 1, 'removed', 'test remove', '2012-11-19 10:51:36'),
(53, 'agent', 'ops', 1, 'removed', 'test remarks - no AFN', '2012-11-19 10:55:06'),
(43, 'agent', 'ops', 1, 'removed', 'test remarks - no AFN', '2012-11-19 10:55:10'),
(42, 'agent', 'ops', 1, 'removed', 'test remarks - no AFN', '2012-11-19 10:55:14'),
(61, 'agent', 'ops', 9, 'removed', 'bekar', '2012-11-19 10:56:43'),
(83, 'agent', 'ops', 9, 'approved', 'Approved-', '2012-11-19 12:47:46'),
(86, 'agent', 'ops', 5, 'approved', 'Credentials are correct', '2012-11-20 09:40:18'),
(91, 'agent', 'ops', 5, 'approved', 'yes pls approve', '2012-11-22 11:20:32'),
(75, 'agent', 'ops', 5, 'approved', 'yesssss', '2012-11-22 11:27:34'),
(91, 'agent', 'ops', 5, 'blocked', 'Yes please', '2012-11-23 10:10:22'),
(41, 'agent', 'ops', 5, 'unblocked', 'yes his details are fine', '2012-11-23 10:10:42'),
(41, 'agent', 'ops', 5, 'unlocked', 'yes please unlock do tthat he can login again', '2012-11-23 10:11:51');

-- --------------------------------------------------------

--
-- Table structure for table `_t_cardholders`
--

CREATE TABLE IF NOT EXISTS `_t_cardholders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `crn` varchar(30) CHARACTER SET utf8 DEFAULT NULL,
  `email` varchar(50) CHARACTER SET utf8 NOT NULL,
  `title` enum('mr','mrs','ms','dr','prof') CHARACTER SET utf8 NOT NULL,
  `first_name` varchar(20) CHARACTER SET utf8 DEFAULT NULL,
  `middle_name` varchar(20) CHARACTER SET utf8 DEFAULT NULL,
  `last_name` varchar(20) CHARACTER SET utf8 DEFAULT NULL,
  `mobile_country_code` varchar(6) CHARACTER SET utf8 DEFAULT NULL,
  `mobile_number` varchar(15) CHARACTER SET utf8 DEFAULT NULL,
  `activation_id` int(11) NOT NULL,
  `activation_status` enum('sucess','failed','pending') NOT NULL,
  `enroll_status` enum('approved','pending') NOT NULL,
  `status` enum('blocked','unblocked') NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=142 ;

--
-- Dumping data for table `_t_cardholders`
--

INSERT INTO `_t_cardholders` (`id`, `product_id`, `crn`, `email`, `title`, `first_name`, `middle_name`, `last_name`, `mobile_country_code`, `mobile_number`, `activation_id`, `activation_status`, `enroll_status`, `status`) VALUES
(1, 0, NULL, '', 'mr', NULL, NULL, NULL, NULL, NULL, 0, 'sucess', 'approved', 'blocked'),
(2, 0, NULL, '', 'mr', NULL, NULL, NULL, NULL, NULL, 0, 'sucess', 'approved', 'blocked'),
(3, 0, NULL, '', 'mr', NULL, NULL, NULL, NULL, NULL, 0, 'sucess', 'approved', 'blocked'),
(4, 0, NULL, '', 'mr', NULL, NULL, NULL, NULL, NULL, 0, 'sucess', 'approved', 'blocked'),
(5, 0, NULL, '', 'mr', NULL, NULL, NULL, NULL, NULL, 0, 'sucess', 'approved', 'blocked'),
(6, 0, NULL, '', 'mr', NULL, NULL, NULL, NULL, NULL, 0, 'sucess', 'approved', 'blocked'),
(7, 0, NULL, '', 'mr', NULL, NULL, NULL, NULL, NULL, 0, 'sucess', 'approved', 'blocked'),
(8, 0, NULL, '', 'mr', NULL, NULL, NULL, NULL, NULL, 0, 'sucess', 'approved', 'blocked'),
(9, 0, NULL, '', 'mr', NULL, NULL, NULL, NULL, NULL, 0, 'sucess', 'approved', 'blocked'),
(10, 99, NULL, '', 'mr', 'ashi', 'ji', 'vats', '91', '999999999', 0, 'sucess', 'approved', 'blocked'),
(11, 99, NULL, '', 'mr', 'ashi', 'ji', 'vats', '91', '999999999', 0, 'sucess', 'approved', 'blocked'),
(12, 99, NULL, '', 'mr', 'ashi', 'ji', 'vats', '91', '999999999', 0, 'sucess', 'approved', 'blocked'),
(13, 99, '', '', 'mr', 'ashi', 'ji', 'vats', '91', '999999999', 0, 'sucess', 'approved', 'blocked'),
(14, 99, NULL, '', 'mr', 'ashi', 'ji', 'vats', '91', '999999999', 0, 'sucess', 'approved', 'blocked'),
(15, 99, NULL, '', 'mr', 'ashi', 'ji', 'vats', '91', '999999999', 0, 'sucess', 'approved', 'blocked'),
(16, 99, NULL, '', 'mr', 'ashi', 'ji', 'vats', '91', '999999999', 0, 'sucess', 'approved', 'blocked'),
(17, 99, NULL, '', 'mr', 'ashi', 'ji', 'vats', '91', '999999999', 0, 'sucess', 'approved', 'blocked'),
(18, 99, NULL, '', 'mr', 'ashi', 'ji', 'vats', '91', '999999999', 0, 'sucess', 'approved', 'blocked'),
(19, 99, NULL, '', 'mr', 'ashi', 'ji', 'vats', '91', '999999999', 0, 'sucess', 'approved', 'blocked'),
(20, 99, NULL, '', 'mr', 'ashi', 'ji', 'vats', '91', '999999999', 0, 'sucess', 'approved', 'blocked'),
(21, 99, NULL, '', 'mr', 'ashi', 'ji', 'vats', '91', '999999999', 0, 'sucess', 'approved', 'blocked'),
(22, 99, NULL, '', 'mr', 'ashi', 'ji', 'vats', '91', '999999999', 0, 'sucess', 'approved', 'blocked'),
(23, 99, NULL, '', 'mr', 'ashi', 'ji', 'vats', '91', '999999999', 0, 'sucess', 'approved', 'blocked'),
(24, 99, NULL, '', 'mr', 'ashi', 'ji', 'vats', '91', '999999999', 0, 'sucess', 'approved', 'blocked'),
(25, 99, NULL, '', 'mr', 'ashi', 'ji', 'vats', '91', '999999999', 0, 'sucess', 'approved', 'blocked'),
(26, 99, NULL, '', 'mr', NULL, NULL, NULL, NULL, NULL, 0, 'sucess', 'approved', 'blocked'),
(27, 99, NULL, '', 'mr', NULL, NULL, NULL, NULL, NULL, 0, 'sucess', 'approved', 'blocked'),
(28, 99, NULL, '', 'mr', 'ashish', 'ji', 'vats', '91', '2147483647', 0, 'sucess', 'approved', 'blocked'),
(29, 99, NULL, '', 'mr', 'ashish', 'ji', 'vats', '91', '2147483647', 0, 'sucess', 'approved', 'blocked'),
(30, 99, NULL, '', 'mr', 'ashish', 'ji', 'vats', '91', '343434343', 0, 'sucess', 'approved', 'blocked'),
(31, 99, NULL, '', 'mr', 'ashish', 'ji', 'vats', '91', '343434343', 0, 'sucess', 'approved', 'blocked'),
(32, 99, NULL, '', 'mr', 'ashish', 'ji', 'vats', '91', '343434343', 0, 'sucess', 'approved', 'blocked'),
(33, 99, NULL, '', 'mr', 'ashish', 'ji', 'vats', '91', '343434343', 0, 'sucess', 'approved', 'blocked'),
(34, 99, NULL, '', 'mr', 'ashish', 'ji', 'vats', '91', '343434343', 0, 'sucess', 'approved', 'blocked'),
(35, 99, NULL, '', 'mr', 'ashish', 'ji', 'vats', '91', '343434343', 0, 'sucess', 'approved', 'blocked'),
(36, 99, NULL, '', 'mr', 'ashish', 'ji', 'vats', '91', '343434343', 0, 'sucess', 'approved', 'blocked'),
(37, 99, NULL, '', 'mr', 'ashish', 'ji', 'vats', '91', '343434343', 0, 'sucess', 'approved', 'blocked'),
(38, 99, NULL, '', 'mr', 'ashish', 'ji', 'vats', '91', '343434343', 0, 'sucess', 'approved', 'blocked'),
(39, 1, NULL, '', 'mr', '', '', '', '+91', '1234567892', 0, 'sucess', 'approved', 'blocked'),
(40, 1, NULL, '', 'mr', 'ashish', 'Kumar', 'vats', '+91', '1234567899', 0, 'sucess', 'approved', 'blocked'),
(41, 1, NULL, '', 'mr', 'ashish', 'Kumar', 'vats', '+91', '1234567123', 0, 'sucess', 'approved', 'blocked'),
(42, 1, NULL, '', 'mr', 'ashish', 'kumar', 'vats', '+91', '1234567895', 0, 'sucess', 'approved', 'blocked'),
(43, 1, NULL, '', 'mr', 'ashish', 'Kumar', 'vats', '+91', '2147483647', 0, 'sucess', 'approved', 'blocked'),
(44, 1, NULL, '', 'mr', 'Jit', 'Varish', 'Tiwari', '+91', '2147483647', 0, 'sucess', 'approved', 'blocked'),
(45, 1, NULL, '', 'mr', 'ashish', 'Kumar', 'vats', '+91', '2147483647', 0, 'sucess', 'approved', 'blocked'),
(46, 1, NULL, '', 'mr', 'ashish', '', '', '+91', '1234567891', 0, 'sucess', 'approved', 'blocked'),
(47, 1, NULL, '', 'mr', 'ashish', '', '', '+91', '2147483647', 0, 'sucess', 'approved', 'blocked'),
(48, 1, NULL, '', 'mr', 'dfds', '', '', '+91', '2147483647', 0, 'sucess', 'approved', 'blocked'),
(49, 1, NULL, '', 'mr', 'dfds', 'aaaa', 'ssss', '+91', '2147483647', 0, 'sucess', 'approved', 'blocked'),
(50, 1, NULL, '', 'mr', 'dfds', 'aaaa', 'ssss', '+91', '2147483647', 0, 'sucess', 'approved', 'blocked'),
(51, 1, NULL, '', 'mr', 'dfds', 'aaaa', 'ssss', '+91', '2147483647', 0, 'sucess', 'approved', 'blocked'),
(52, 1, NULL, '', 'mr', 'dfds', 'aaaa', 'ssss', '+91', '2147483647', 0, 'sucess', 'approved', 'blocked'),
(53, 1, NULL, '', 'mr', 'dfds', 'aaaa', 'ssss', '+91', '2147483647', 0, 'sucess', 'approved', 'blocked'),
(54, 1, NULL, '', 'mr', 'dfds', 'aaaa', 'ssss', '+91', '2147483647', 0, 'sucess', 'approved', 'blocked'),
(55, 1, NULL, '', 'mr', 'dfds', 'aaaa', 'ssss', '+91', '2147483647', 0, 'sucess', 'approved', 'blocked'),
(56, 1, NULL, '', 'mr', 'dfds', 'aaaa', 'ssss', '+91', '2147483647', 0, 'sucess', 'approved', 'blocked'),
(57, 1, NULL, '', 'mr', 'dfds', 'aaaa', 'ssss', '+91', '2147483647', 0, 'sucess', 'approved', 'blocked'),
(58, 1, NULL, '', 'mr', 'dfds', 'aaaa', 'ssss', '+91', '2147483647', 0, 'sucess', 'approved', 'blocked'),
(59, 1, NULL, '', 'mr', 'dfds', 'aaaa', 'ssss', '+91', '2147483647', 0, 'sucess', 'approved', 'blocked'),
(60, 1, NULL, '', 'mr', 'asdf', '', 'asdf', '+91', '2147483647', 0, 'sucess', 'approved', 'blocked'),
(61, 1, NULL, '', 'mr', 'Vikram', '', 'Singh', '+91', '2147483647', 0, 'sucess', 'approved', 'blocked'),
(62, 1, NULL, '', 'mr', 'Vikram', '', 'Singh', '+91', '2147483647', 0, 'sucess', 'approved', 'blocked'),
(63, 1, NULL, '', 'mr', 'Vikram', '', 'Singh', '+91', '9899195914', 0, 'sucess', 'approved', 'blocked'),
(64, 1, NULL, '', 'mr', 'rahul', 'kumar', 'jain', '+91', '3434343433', 0, 'sucess', 'approved', 'blocked'),
(65, 1, NULL, '', 'mr', 'rahul', 'kumar', 'jain', '+91', '9885558899', 0, 'sucess', 'approved', 'blocked'),
(66, 1, NULL, '', 'mr', 'Akshay', '', 'Kumar', '+91', '9810256897', 0, 'sucess', 'approved', 'blocked'),
(67, 1, NULL, '', 'mr', 'rahul', 'kumar', 'jain', '+91', '4545454544', 0, 'sucess', 'approved', 'blocked'),
(68, 1, NULL, '', 'mr', 'Nonie', '', 'Janith', '+91', '9810212345', 0, 'sucess', 'approved', 'blocked'),
(69, 1, NULL, '', 'mr', 'Manoj', '', 'Kumar', '+91', '9810012345', 0, 'sucess', 'approved', 'blocked'),
(70, 1, NULL, '', 'mr', 'aaa', '', 'aaaa', '+91', '9899999999', 0, 'sucess', 'approved', 'blocked'),
(71, 1, NULL, '', 'mr', 'Manoj', '', 'Kumar', '+91', '9810012346', 0, 'sucess', 'approved', 'blocked'),
(72, 1, NULL, '', 'mr', 'ddd', '', 'sss', '+91', '9811111111', 0, 'sucess', 'approved', 'blocked'),
(73, 1, NULL, '', 'mr', 'Vikram', '', 'Singh', '+91', '9899195333', 0, 'sucess', 'approved', 'blocked'),
(74, 1, NULL, '', 'mr', 'Soniya', '', 'Janith', '+91', '9810112345', 0, 'sucess', 'approved', 'blocked'),
(75, 1, NULL, '', 'mr', 'Manoj', '', 'Kumar', '+91', '3434349543', 0, 'sucess', 'approved', 'blocked'),
(76, 1, NULL, '', 'mr', 'ashish', '', 'vats', '+91', '9885558891', 0, 'sucess', 'approved', 'blocked'),
(77, 1, NULL, '', 'mr', 'ashish', '', 'vats', '+91', '9885558123', 0, 'sucess', 'approved', 'blocked'),
(78, 1, NULL, '', 'mr', 'ashish', '', 'vats', '+91', '3431231232', 0, 'sucess', 'approved', 'blocked'),
(79, 1, 'crn00001', '', 'mr', 'ashish', '', 'vats', '+91', '1234567896', 0, 'sucess', 'approved', 'blocked'),
(80, 1, 'crn00002', '', 'mr', 'vivek', 'kumar', 'sharma', '+91', '9810236548', 0, 'sucess', 'approved', 'blocked'),
(81, 1, 'crn00003', '', 'mr', 'Robin', '', 'Saxena', '+91', '9810123456', 0, 'sucess', 'approved', 'blocked'),
(82, 1, 'crn00004', '', 'mr', 'Raj', '', 'kumar', '+91', '9885525612', 0, 'sucess', 'approved', 'blocked'),
(83, 2, 'crn00005', '', 'mr', 'Jit', '', 'kumar', '+91', '3434343456', 0, 'sucess', 'approved', 'blocked'),
(84, 2, 'crn00006', '', 'mr', 'peter', '', 'jha', '+91', '9810212347', 0, 'sucess', 'approved', 'blocked'),
(85, 1, 'crn00007', '', 'mr', 'Vikram', '', 'Singh', '+91', '9899191919', 0, 'sucess', 'approved', 'blocked'),
(86, 1, 'crn00008', '', 'mr', 'Manoj', '', 'Kumar', '+91', '9885525369', 0, 'sucess', 'approved', 'blocked'),
(88, 1, 'crn00010', '', 'mr', 'Vijay', '', 'Singh', '+91', '9810112398', 0, 'sucess', 'approved', 'blocked'),
(89, 1, 'crn00011', '', 'mr', 'Raj', '', 'Kumar', '+91', '9875556985', 0, 'sucess', 'approved', 'blocked'),
(90, 3, 'crn00012', '', 'mr', 'neeta', '', 'saini', '+91', '9811112345', 0, 'sucess', 'approved', 'blocked'),
(91, 3, 'crn00013', '', 'mr', 'Rohan', '', 'Kumar', '+91', '9885500123', 0, 'sucess', 'approved', 'blocked'),
(92, 1, 'crn00014', '', 'mr', 'jit', '', 'varish', '+91', '9880302058', 0, 'sucess', 'approved', 'blocked'),
(93, 3, 'crn00015', '', 'mr', 'Rajender', '', 'Kumar', '+91', '9866655501', 0, 'sucess', 'approved', 'blocked'),
(95, 3, 'crn00017', '', 'mr', 'Neeraj', '', 'Jha', '+91', '9810104078', 0, 'sucess', 'approved', 'blocked'),
(96, 3, 'crn00018', '', 'mr', 'Ashok', '', 'Jain', '+91', '9836997451', 0, 'sucess', 'approved', 'blocked'),
(97, 3, 'crn00019', '', 'mr', 'Ram', '', 'Prakash', '+91', '9923569847', 0, 'sucess', 'approved', 'blocked'),
(98, 3, 'crn00020', '', 'prof', 'Ram', '', 'Prakash', '+91', '9811112340', 0, 'sucess', 'approved', 'blocked'),
(99, 3, 'crn00021', '', 'mr', 'Raj', '', 'Anand', '+91', '9810317882', 0, 'sucess', 'approved', 'blocked'),
(100, 3, 'crn00022', '', 'mr', 'ashish', '', 'Prakash', '+91', '3434341234', 0, 'sucess', 'approved', 'blocked'),
(101, 3, 'crn00023', 'test@test.com', 'dr', 'Ramneek', '', 'Kumar', '+91', '9885521234', 0, 'sucess', 'approved', 'blocked'),
(105, 1, 'crn00027', 'robin8@test.com', 'mr', 'Manoj', '', 'Prakash', '+91', '3434120000', 0, 'sucess', 'approved', 'blocked'),
(106, 3, 'crn00028', 'ashish4444@transerv.co.in', 'mr', 'Ashish', '', 'Vats', '+91', '9712345698', 0, 'sucess', 'approved', 'blocked'),
(107, 3, 'crn00029', 'robin9@test.com', 'dr', 'Raj', '', 'Prakash', '+91', '9632145872', 0, 'sucess', 'approved', 'blocked'),
(108, 3, 'crn00030', 'ashi@transerv.co.in', 'mr', 'ashish', '', 'vats', '+91', '9512345698', 0, 'sucess', 'approved', 'blocked'),
(109, 3, 'crn00031', 'anand@anand.com', 'mr', 'ashish', '', 'vats', '+91', '9815515455', 0, 'sucess', 'approved', 'blocked'),
(110, 3, 'crn00032', 'adit@test.com', 'mr', 'Adit', '', 'Jain', '+91', '9878896989', 0, 'sucess', 'approved', 'blocked'),
(111, 3, 'crn00033', 'ashish12344@transerv.co.in', 'mr', 'Ashish', '', 'vats', '+91', '9810123567', 0, 'sucess', 'approved', 'blocked'),
(112, 1, 'crn00034', 'robin3333@test.com', 'mr', 'Ram', '', 'Kumar', '+91', '3434343466', 0, 'sucess', 'approved', 'blocked'),
(113, 3, 'crn00035', 'ash@transerv.co.in', 'mr', 'Ash', '', 'Jain', '+91', '9811228899', 0, 'sucess', 'approved', 'blocked'),
(114, 3, NULL, 'ashish1@transerv.co.in', 'mr', 'ashish', '', 'Kumar', '+91', '6589321478', 0, 'sucess', 'approved', 'blocked'),
(115, 3, NULL, 'ashish11@transerv.co.in', 'mr', 'ashish', '', 'Kumar', '+91', '1123698745', 0, 'sucess', 'approved', 'blocked'),
(116, 3, NULL, 'raj@test.com', 'mr', 'Raj', '', 'Jilani', '+91', '9811002233', 0, 'sucess', 'approved', 'blocked'),
(117, 1, NULL, 'rajjj@transerv.co.in', 'mr', 'Rajinder1', '', 'Kumar1', '+91', '9885111111', 0, 'sucess', 'approved', 'blocked'),
(118, 1, NULL, 'aaaaaaa@transerv.co.in', 'mr', 'Manoj', '', 'kumar', '+91', '9977889988', 0, 'sucess', 'approved', 'blocked'),
(119, 1, NULL, 'aadfdfdf@transerv.co.in', 'mr', 'Ram', '', 'Prakash', '+91', '9711589621', 0, 'sucess', 'approved', 'blocked'),
(120, 3, NULL, 'amar@test.com', 'mr', 'Amar1', '', 'Singh', '+91', '9878895656', 0, 'sucess', 'approved', 'blocked'),
(121, 3, NULL, 'ashish1234@transerv.co.in', 'mr', 'Rajesh', '', 'Kumar', '+91', '9810236985', 0, 'pending', 'pending', 'unblocked'),
(122, 3, NULL, 'manoj12345@transerv.co.in', 'mr', 'Manoj1', '', 'Kumar', '+91', '1234598745', 0, 'pending', 'pending', 'unblocked'),
(123, 3, NULL, 'amarnath@test.com', 'mr', 'Raj', '', 'Jain', '+91', '9678931456', 0, 'pending', 'pending', 'unblocked'),
(124, 3, NULL, 'rajnish@test.com', 'mr', 'Rajeshish', '', 'Kumar', '+91', '9711345678', 0, 'pending', 'pending', 'unblocked'),
(125, 3, NULL, 'rajraj@test.com', 'mr', 'Raj', '', 'Jain', '+91', '9632178965', 0, 'pending', 'pending', 'unblocked'),
(126, 1, NULL, 'adfasd@test.com', 'mr', 'ashish', '', 'Kumar', '+91', '9878812345', 0, 'pending', 'pending', 'unblocked'),
(127, 0, NULL, 'sdfas@fsdf.com', 'mr', 'afds', '', 'sdfas', '+91', '5656565345', 0, 'pending', 'pending', 'unblocked'),
(128, 10, NULL, 'test1@test.com', 'mr', 'Ashok', '', 'Kumar', '+91', '1234567878', 0, 'pending', 'pending', 'unblocked'),
(129, 10, NULL, 'neeraj@test.com', 'mr', 'Neeraj', '', 'Kumar', '+91', '9711198123', 0, 'pending', 'pending', 'unblocked'),
(130, 10, 'crn00073', 'neeraj11@test.com', 'mr', 'Neeraj', '', 'Kumar', '+91', '9711198545', 0, 'pending', 'pending', 'unblocked'),
(131, 1, 'crn00089', 'neerajdfh@test.com', 'mr', 'Abhishek', '', 'Bisht', '+91', '1234551236', 0, 'pending', 'pending', 'unblocked'),
(132, 1, 'crn00097', 'teat2@fdfs.com', 'mr', 'akash', '', 'jain', '+91', '3654123323', 0, 'pending', 'pending', 'unblocked'),
(133, 1, 'crn000102', 'teatdf2@fdfs.com', 'mr', 'aa', '', 'fsdf', '+91', '3654123312', 0, 'pending', 'pending', 'unblocked'),
(134, 1, 'crn000108', 'teatsss2@fdfs.com', 'mr', 'akash', '', 'jain', '+91', '1236547895', 0, 'pending', 'pending', 'unblocked'),
(135, 1, 'crn000114', 'neeleshdsfas@test.com', 'mr', 'Neelesh', '', 'Anand', '+91', '1236547777', 0, 'pending', 'pending', 'unblocked'),
(136, 1, 'crn000119', 'tedfat2@fdfs.com', 'mr', 'akash', '', 'Jain', '+91', '1236547987', 0, 'pending', 'pending', 'unblocked'),
(137, 1, 'crn000123', 'vikram0207@gmail.com', 'mr', 'Vikram', '', 'Singh', '+91', '1236555558', 0, 'pending', 'pending', 'unblocked'),
(138, 1, NULL, 'tedfsdat2@fdfs.com', 'mr', 'akash', '', 'jain', '+91', '9711198518', 0, 'pending', 'pending', 'unblocked'),
(139, 1, 'crn000127', 'jit@transerv.co.in', 'mr', 'Jit', '', 'Varish', '+91', '9800000000', 0, 'pending', 'pending', 'unblocked'),
(140, 1, 'crn000130', 'needdas@test.com', 'mr', 'akash', '', 'Varish', '+91', '3215487889', 0, 'pending', 'pending', 'unblocked'),
(141, 1, 'crn000133', 'ashish@transerv.co.in', 'mr', 'Neelesh', '', 'Anand', '+91', '9810780690', 0, 'sucess', 'approved', 'blocked');

--
-- Constraints for dumped tables
--

--
-- Constraints for table `t_agent_balance`
--
ALTER TABLE `t_agent_balance`
  ADD CONSTRAINT `t_agent_balance_ibfk_1` FOREIGN KEY (`agent_id`) REFERENCES `t_agents` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `t_agent_products`
--
ALTER TABLE `t_agent_products`
  ADD CONSTRAINT `t_agent_products_ibfk_1` FOREIGN KEY (`agent_id`) REFERENCES `t_agents` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `t_agent_products_ibfk_2` FOREIGN KEY (`fee_id`) REFERENCES `t_fee` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `t_agent_transactions`
--
ALTER TABLE `t_agent_transactions`
  ADD CONSTRAINT `t_agent_transactions_ibfk_1` FOREIGN KEY (`agent_id`) REFERENCES `t_agents` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `t_fee`
--
ALTER TABLE `t_fee`
  ADD CONSTRAINT `fk_product_id` FOREIGN KEY (`product_id`) REFERENCES `t_products` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `t_fee_items`
--
ALTER TABLE `t_fee_items`
  ADD CONSTRAINT `fk_fee_id` FOREIGN KEY (`fee_id`) REFERENCES `t_fee` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `t_products`
--
ALTER TABLE `t_products`
  ADD CONSTRAINT `fk_bank_idp` FOREIGN KEY (`bank_id`) REFERENCES `t_bank` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
