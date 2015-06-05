ALTER TABLE  `t_beneficiaries` CHANGE  `mobile`  `mobile` VARCHAR( 20 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT  '0',
CHANGE  `email`  `email` VARCHAR( 60 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL, CHANGE  `branch_address`  `branch_address` VARCHAR( 350 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL;
