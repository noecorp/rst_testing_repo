INSERT INTO `t_products` VALUES ('22', '3', 'RATNAKAR HFCI', 'RBL HFCI', 'INR', '10000049', 'DigiWallet', '922', 'RAT_HFCI', 'yes', 'no', '101', '127000000001', NOW(), 'active');

INSERT INTO `purse_master` VALUES ('22', '3', '22', '1', 'HOL922', 'HFCI Open Loop Loan Wallet', 'RBL HFCI Open Loop Loan Wallet', '50000', 'no', 'no', 'api', '0', '0', '0', '1', '50000', '0', '0', '0', '0', '0', '0', 'none', 'yes', '0', '0', '0', '0', '0', '0', '0', '0', '1', NOW(), NOW(), NOW(), '101', 'active');
INSERT INTO `purse_master` VALUES ('23', '3', '22', '1', 'HCR922', 'HFCI Close Loop Repayment Wallet', 'RBL HFCI Close Loop Repayment Wallet', '50000', 'no', 'no', 'api', '0', '0', '0', '1', '50000', '0', '0', '0', '0', '0', '0', 'none', 'yes', '0', '0', '0', '0', '0', '0', '0', '0', '2', NOW(), NOW(), NOW(), '101', 'active');

INSERT INTO `product_customer_limits` VALUES ('35', '3', '22', 'KYC922', 'kyc', 'KYC RBL HFCI', 'KYC RBL HFCI', '10000', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', NOW(), NOW(), NOW(), '101', 'active');
INSERT INTO `product_customer_limits` VALUES ('36', '3', '22', 'NKC922', 'non-kyc', 'Non-KYC RBL HFCI', 'Non-KYC RBL HFCI', '50000', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', NOW(), NOW(), NOW(), '101', 'active');