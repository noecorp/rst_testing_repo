DROP TABLE IF EXISTS `bank_statement`;
CREATE TABLE `bank_statement` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `bank_stt_name` varchar(50) DEFAULT NULL,
  `txn_date` varchar(20) DEFAULT NULL,
  `description` varchar(100) DEFAULT NULL,
  `journal_no` varchar(50) DEFAULT NULL,
  `cheque_no` varchar(50) DEFAULT NULL,
  `mode` enum('cr','dr') DEFAULT NULL,
  `amount` decimal(11,2) DEFAULT NULL,
  `balance` decimal(11,2) DEFAULT NULL,
  `status` enum('new','duplicate','unsettled','settled') NOT NULL DEFAULT 'new',
  `by_ops_id` int(11) unsigned NOT NULL,
  `ip` varchar(15) NOT NULL,
  `date_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of bank_statement
-- ----------------------------

DROP TABLE IF EXISTS `agent_funding`;
CREATE TABLE `agent_funding` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `agent_id` int(11) unsigned NOT NULL,
  `amount` decimal(11,2) NOT NULL DEFAULT '0.00',
  `fund_transfer_type_id` int(11) unsigned NOT NULL,
  `journal_no` varchar(50) DEFAULT NULL,
  `cheque_no` varchar(50) DEFAULT NULL,
  `cheque_details` varchar(255) DEFAULT NULL,
  `comments` varchar(255) NOT NULL,
  `approved_by` enum('system','ops') DEFAULT NULL,
  `by_ops_id` int(11) unsigned DEFAULT NULL,
  `ip_agent` varchar(15) NOT NULL,
  `ip_ops` varchar(15) DEFAULT NULL,
  `date_request` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_settlement` timestamp NULL DEFAULT NULL,
  `settlement_remarks` varchar(255) DEFAULT NULL,
  `status` enum('approve','pending','decline') NOT NULL DEFAULT 'pending',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=41 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of agent_funding
-- ----------------------------
INSERT INTO `agent_funding` VALUES ('1', '106', '5000.00', '0', '2', null, null, 'CHEQUEN NUMBER 123456', null, null, '', null, '2013-01-15 16:22:19', null, null, 'decline');
INSERT INTO `agent_funding` VALUES ('2', '106', '10000.00', '0', '2', null, null, 'chq no 123456', null, null, '', null, '2013-01-15 16:30:26', null, null, 'approve');
INSERT INTO `agent_funding` VALUES ('3', '106', '100.00', '0', '2', null, null, 'chq 542145', null, null, '', null, '2013-01-15 16:32:08', null, null, 'approve');
INSERT INTO `agent_funding` VALUES ('4', '106', '10000.00', '0', '3', null, null, 'NEFT reference no. 56478', null, null, '', null, '2013-01-15 19:31:53', null, null, 'approve');
INSERT INTO `agent_funding` VALUES ('5', '106', '10000.00', '0', '2', null, null, 'Cheque No. 078653', null, null, '', null, '2013-01-15 19:38:26', null, null, 'approve');
INSERT INTO `agent_funding` VALUES ('6', '140', '1000.00', '0', '3', null, null, 'requested for funds auth', null, null, '', null, '2013-01-22 15:19:39', null, null, 'decline');
INSERT INTO `agent_funding` VALUES ('7', '106', '10000.00', '0', '2', null, null, 'cheque number 546321\r\nCheque date 25/Jan/13', null, null, '', null, '2013-01-22 19:47:54', null, null, 'approve');
INSERT INTO `agent_funding` VALUES ('8', '144', '100000.00', '0', '1', null, null, 'PLease approve fund request\r\n\r\nPP', null, null, '', null, '2013-01-22 20:00:32', null, null, 'approve');
INSERT INTO `agent_funding` VALUES ('9', '140', '20000.00', '0', '3', null, null, '20000 Rs NEFT details\r\nTo Axis Bank Account.', null, null, '', null, '2013-01-24 18:19:27', null, null, 'approve');
INSERT INTO `agent_funding` VALUES ('10', '140', '10000.00', '0', '3', null, null, 'NEFT REFERENCE NUMBER - 2349239423\r\nAmount 10000\r\nbank name\r\ndate today', null, null, '', null, '2013-02-01 15:00:21', null, null, 'approve');
INSERT INTO `agent_funding` VALUES ('11', '106', '1000.00', '0', '1', null, null, 'Cash Deposit....', null, null, '', null, '2013-02-06 17:20:43', null, null, 'approve');
INSERT INTO `agent_funding` VALUES ('12', '106', '1000.00', '0', '2', null, null, 'chq no 547854 dated 05/03/2012', null, null, '', null, '2013-02-06 17:47:57', null, null, 'decline');
INSERT INTO `agent_funding` VALUES ('13', '106', '100.00', '0', '1', null, null, 'cash deposit', null, null, '', null, '2013-02-06 19:18:30', null, null, 'approve');
INSERT INTO `agent_funding` VALUES ('14', '106', '10001.00', '0', '1', null, null, 'cash deposit', null, null, '', null, '2013-02-06 19:19:37', null, null, 'approve');
INSERT INTO `agent_funding` VALUES ('15', '106', '1000.00', '0', '2', null, null, 'Point taken', null, null, '', null, '2013-02-09 22:28:51', null, null, 'decline');
INSERT INTO `agent_funding` VALUES ('16', '146', '150000.00', '0', '2', null, null, 'Please give me cheque worth 150000', null, null, '', null, '2013-02-19 18:14:01', null, null, 'approve');
INSERT INTO `agent_funding` VALUES ('17', '146', '1000000.00', '0', '4', null, null, 'Please send me DD', null, null, '', null, '2013-02-20 18:30:26', null, null, 'decline');
INSERT INTO `agent_funding` VALUES ('18', '151', '2000.00', '0', '2', null, null, 'cheque no. 12341', null, null, '', null, '2013-02-20 18:31:47', null, null, 'approve');
INSERT INTO `agent_funding` VALUES ('19', '146', '1000.00', '0', '1', null, null, 'Give me chash', null, null, '', null, '2013-02-20 18:33:32', null, null, 'approve');
INSERT INTO `agent_funding` VALUES ('20', '146', '1000.00', '0', '1', null, null, 'Please deposit cash', null, null, '', null, '2013-02-20 20:35:15', null, null, 'approve');
INSERT INTO `agent_funding` VALUES ('21', '146', '1000.00', '0', '1', null, null, 'cash deposit', null, null, '', null, '2013-02-20 21:16:37', null, null, 'decline');
INSERT INTO `agent_funding` VALUES ('22', '154', '100.00', '0', '1', null, null, 'please deposit that amount in my account.', null, null, '', null, '2013-02-21 20:02:48', null, null, 'approve');
INSERT INTO `agent_funding` VALUES ('23', '154', '200.00', '0', '1', null, null, 'Please credit that amount in my account, as i deposited that amount in shmart account.', null, null, '', null, '2013-02-22 18:06:13', null, null, 'approve');
INSERT INTO `agent_funding` VALUES ('24', '140', '5000.00', '0', '3', null, null, 'neft ref no 3457678908\r\ndate : 27 feb 2013\r\nbank name - axis bank\r\nbranch - andheri\r\nmumbai', null, null, '', null, '2013-02-28 15:00:34', null, null, 'approve');
INSERT INTO `agent_funding` VALUES ('25', '106', '5000.00', '0', '2', null, null, 'Chq No 521452\r\nAmount 5000\r\nBank - Bank of maharashtra\r\nBranch - Kandivali\r\nChq date - 25/Feb/2013', null, null, '', null, '2013-02-28 20:18:51', null, null, 'approve');
INSERT INTO `agent_funding` VALUES ('26', '151', '50000.00', '0', '4', null, null, 'dd no. 12345', null, null, '', null, '2013-03-01 20:09:13', null, null, 'approve');
INSERT INTO `agent_funding` VALUES ('27', '146', '10000.00', '0', '1', null, null, 'Pls deposit cash', null, null, '', null, '2013-03-09 20:47:06', null, null, 'approve');
INSERT INTO `agent_funding` VALUES ('28', '140', '10000.00', '0', '3', null, null, 'neft code', null, null, '', null, '2013-03-10 16:34:02', null, null, 'approve');
INSERT INTO `agent_funding` VALUES ('29', '140', '10000.00', '0', '3', null, null, 'bank details\r\nbank branch\r\ndate\r\namount\r\n', null, null, '', null, '2013-03-14 17:25:29', null, null, 'approve');
INSERT INTO `agent_funding` VALUES ('30', '140', '5000.00', '0', '2', null, null, 'deposit clear status\r\ncheq number 348308\r\ndate\r\nbank', null, null, '', null, '2013-03-14 17:26:07', null, null, 'decline');
INSERT INTO `agent_funding` VALUES ('31', '140', '2500.00', '0', '4', null, null, 'draft given for credit\r\nbank name', null, null, '', null, '2013-03-14 18:15:08', null, null, 'decline');
INSERT INTO `agent_funding` VALUES ('32', '154', '150000.00', '0', '1', null, null, 'pls deposit cash', null, null, '', null, '2013-04-04 11:59:21', null, null, 'approve');
INSERT INTO `agent_funding` VALUES ('33', '154', '10000.00', '0', '1', null, null, 'kwhwfkjhasj', null, null, '', null, '2013-04-17 11:45:19', null, null, 'approve');
INSERT INTO `agent_funding` VALUES ('34', '151', '30000.00', '0', '3', null, null, 'neft transfer done', null, null, '', null, '2013-06-05 00:02:05', null, null, 'pending');
INSERT INTO `agent_funding` VALUES ('35', '151', '100000.00', '0', '2', null, null, 'cheque no. 123243', null, null, '', null, '2013-06-05 00:02:27', null, null, 'pending');
INSERT INTO `agent_funding` VALUES ('36', '151', '25000.00', '0', '2', null, null, 'any no. shklqk', null, null, '', null, '2013-06-08 04:11:46', null, null, 'approve');
INSERT INTO `agent_funding` VALUES ('37', '151', '2000.00', '0', '4', null, null, 'dd no goes here', null, null, '', null, '2013-06-08 04:33:58', null, null, 'approve');
INSERT INTO `agent_funding` VALUES ('38', '151', '15000.00', '0', '2', null, null, 'cheque no. 63839', null, null, '', null, '2013-06-08 04:44:43', null, null, 'pending');
INSERT INTO `agent_funding` VALUES ('39', '168', '2000.00', '0', '2', null, null, 'cheque coming', null, null, '', null, '2013-06-08 04:52:51', null, null, 'pending');
INSERT INTO `agent_funding` VALUES ('40', '168', '24000.00', '0', '1', null, null, 'cash deposit by test', null, null, '', null, '2013-09-05 16:31:25', null, null, 'pending');