-- ----------------------------
-- Records of t_bank
-- ----------------------------
INSERT INTO `t_bank` VALUES ('1', 'AXIS BANK', 'UTIB0000060', 'MUMBAI', 'WORLI', '264-265, VASWANI CHAMBERS  DR.ANNIE BESANT ROAD,WORLI', '100', '101', '122160080129', '2013-05-14 18:23:57', 'active');
INSERT INTO `t_bank` VALUES ('2', 'BANK OF INDIA', 'BKID0000001', 'GREATER MUMBAI', 'MUMBAI MAIN', 'MUMBAI MAIN,70/80, M.G. ROAD,P.B. NO. 238, MUMBAI,MUMBAI(BOMBAY),INDIA,400023,MAHARASHTRA', '200', '101', '122160080129', '2013-05-14 18:28:59', 'active');

-- ----------------------------
-- Records of t_log_bank
-- ----------------------------
INSERT INTO `t_log_bank` VALUES ('1', 'AXIS BANK', 'UTIB0000060', 'MUMBAI', 'WORLI', '264-265, VASWANI CHAMBERS  DR.ANNIE BESANT ROAD,WORLI', '100', '101', '122160080129', '2013-05-14 18:24:51', 'active');
INSERT INTO `t_log_bank` VALUES ('2', 'BANK OF INDIA', 'BKID0000001', 'GREATER MUMBAI', 'MUMBAI MAIN', 'MUMBAI MAIN,70/80, M.G. ROAD,P.B. NO. 238, MUMBAI,MUMBAI(BOMBAY),INDIA,400023,MAHARASHTRA', '200', '101', '122160080129', '2013-05-14 18:24:54', 'active');

-- ----------------------------
-- Records of t_products
-- ----------------------------
INSERT INTO `t_products` VALUES ('1', '1', 'Axis Bank Shmart!Pay Prepaid Card', 'MVC based Prepaid Card for online payments', 'INR', '10000001', 'Mvc', '110', '101', '122160080129', '2013-05-14 18:25:41', 'active');
INSERT INTO `t_products` VALUES ('2', '2', 'Bank of India Shmart Remit', 'Bank of India Remittance Service', 'INR', '10000002', 'Remit', '210', '101', '122160080129', '2013-05-14 18:26:24', 'active');

-- ----------------------------
-- Records of t_log_products
-- ----------------------------
INSERT INTO `t_log_products` VALUES ('1', '1', 'Axis Bank Shmart!Pay Prepaid Card', 'MVC based Prepaid Card for online payments', 'INR', '10000001', 'Mvc', '110', '101', '122160080129', '2013-05-14 18:25:41', 'active');
INSERT INTO `t_log_products` VALUES ('2', '2', 'Bank of India Shmart Remit', 'Bank of India Remittance Service', 'INR', '10000002', 'Remit', '210', '101', '122160080129', '2013-05-14 18:26:24', 'active');

-- ----------------------------
-- Records of t_product_privileges
-- ----------------------------
INSERT INTO `t_product_privileges` VALUES ('1', '1', '30', '145', '1');
INSERT INTO `t_product_privileges` VALUES ('2', '1', '31', '147', '1');
INSERT INTO `t_product_privileges` VALUES ('3', '1', '31', '148', '1');
INSERT INTO `t_product_privileges` VALUES ('4', '1', '31', '149', '1');
INSERT INTO `t_product_privileges` VALUES ('5', '1', '31', '150', '1');
INSERT INTO `t_product_privileges` VALUES ('6', '1', '42', '152', '1');
INSERT INTO `t_product_privileges` VALUES ('7', '1', '42', '153', '1');
INSERT INTO `t_product_privileges` VALUES ('8', '1', '42', '156', '1');
INSERT INTO `t_product_privileges` VALUES ('9', '1', '42', '157', '1');
INSERT INTO `t_product_privileges` VALUES ('10', '1', '42', '200', '1');
INSERT INTO `t_product_privileges` VALUES ('11', '1', '29', '140', '1');
INSERT INTO `t_product_privileges` VALUES ('12', '1', '29', '141', '1');
INSERT INTO `t_product_privileges` VALUES ('13', '1', '29', '142', '1');
INSERT INTO `t_product_privileges` VALUES ('14', '1', '29', '143', '1');
INSERT INTO `t_product_privileges` VALUES ('15', '1', '29', '144', '1');
INSERT INTO `t_product_privileges` VALUES ('16', '1', '33', '154', '1');
INSERT INTO `t_product_privileges` VALUES ('17', '1', '33', '155', '1');
INSERT INTO `t_product_privileges` VALUES ('18', '1', '33', '173', '1');
INSERT INTO `t_product_privileges` VALUES ('27', '1', '42', '202', '1');
INSERT INTO `t_product_privileges` VALUES ('28', '1', '33', '203', '1');
INSERT INTO `t_product_privileges` VALUES ('29', '1', '33', '204', '1');
INSERT INTO `t_product_privileges` VALUES ('30', '2', '45', '180', '1');
INSERT INTO `t_product_privileges` VALUES ('31', '2', '45', '178', '1');
INSERT INTO `t_product_privileges` VALUES ('32', '2', '45', '179', '1');
INSERT INTO `t_product_privileges` VALUES ('33', '2', '44', '176', '1');
INSERT INTO `t_product_privileges` VALUES ('34', '2', '45', '177', '1');
INSERT INTO `t_product_privileges` VALUES ('35', '2', '45', '201', '1');
INSERT INTO `t_product_privileges` VALUES ('36', '2', '44', '183', '1');
INSERT INTO `t_product_privileges` VALUES ('37', '2', '44', '182', '1');
INSERT INTO `t_product_privileges` VALUES ('38', '2', '45', '181', '1');
INSERT INTO `t_product_privileges` VALUES ('39', '2', '32', '208', '1');
INSERT INTO `t_product_privileges` VALUES ('40', '1', '32', '208', '1');
INSERT INTO `t_product_privileges` VALUES ('41', '2', '32', '209', '1');
INSERT INTO `t_product_privileges` VALUES ('42', '1', '32', '209', '1');
INSERT INTO `t_product_privileges` VALUES ('43', '2', '32', '210', '1');
INSERT INTO `t_product_privileges` VALUES ('44', '1', '32', '210', '1');
INSERT INTO `t_product_privileges` VALUES ('45', '2', '28', '146', '1');
INSERT INTO `t_product_privileges` VALUES ('46', '2', '35', '193', '1');
INSERT INTO `t_product_privileges` VALUES ('47', '2', '35', '194', '1');
INSERT INTO `t_product_privileges` VALUES ('48', '1', '28', '146', '1');
INSERT INTO `t_product_privileges` VALUES ('49', '1', '35', '193', '1');
INSERT INTO `t_product_privileges` VALUES ('50', '1', '35', '194', '1');
INSERT INTO `t_product_privileges` VALUES ('77', '2', '33', '222', '1');
INSERT INTO `t_product_privileges` VALUES ('78', '2', '33', '223', '1');
INSERT INTO `t_product_privileges` VALUES ('79', '1', '33', '224', '1');
INSERT INTO `t_product_privileges` VALUES ('80', '2', '33', '224', '1');
INSERT INTO `t_product_privileges` VALUES ('81', '1', '33', '225', '1');
INSERT INTO `t_product_privileges` VALUES ('82', '2', '33', '225', '1');
INSERT INTO `t_product_privileges` VALUES ('83', '1', '42', '226', '1');
INSERT INTO `t_product_privileges` VALUES ('84', '1', '42', '227', '1');
INSERT INTO `t_product_privileges` VALUES ('85', '1', '33', '228', '1');
INSERT INTO `t_product_privileges` VALUES ('86', '2', '33', '228', '1');
INSERT INTO `t_product_privileges` VALUES ('87', '1', '42', '229', '1');
INSERT INTO `t_product_privileges` VALUES ('88', '1', '42', '230', '1');
INSERT INTO `t_product_privileges` VALUES ('89', '2', '45', '237', '1');
INSERT INTO `t_product_privileges` VALUES ('90', '1', '33', '239', '1');
INSERT INTO `t_product_privileges` VALUES ('91', '1', '33', '240', '1');
INSERT INTO `t_product_privileges` VALUES ('92', '2', '53', '245', '1');
INSERT INTO `t_product_privileges` VALUES ('93', '2', '53', '246', '1');
INSERT INTO `t_product_privileges` VALUES ('96', '1', '33', '253', '1');
INSERT INTO `t_product_privileges` VALUES ('97', '1', '33', '254', '1');

-- ----------------------------
-- Records of t_unicode_conf
-- ----------------------------
INSERT INTO `t_unicode_conf` VALUES ('10011000', '100', '110');
INSERT INTO `t_unicode_conf` VALUES ('20021000', '200', '210');

