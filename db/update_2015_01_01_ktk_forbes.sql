INSERT INTO `api_user` (`id`, `tp_user_id`, `username`, `password`, `status`, `date_created`) VALUES (22, '22', 'forbes', '3a45575180ac157510c80ac1a5244c4eba0762b4', 'active', '2014-09-18 00:00:00');

INSERT INTO `api_user_ip` (`id`, `tp_user_id`, `tp_user_ip`, `date_created`) VALUES (22, '22', '127.0.0.1,122.160.80.129,58.68.41.26,182.71.83.66,14.140.201.162', '2014-09-18 00:00:00');

ALTER TABLE `kotak_remit_remitters` ADD `partner_ref_no` BIGINT( 16 ) NULL;
ALTER TABLE `kotak_remit_remitters` ADD `txnrefnum` BIGINT( 16 ) NULL AFTER `partner_ref_no`;
ALTER TABLE `kotak_remit_remitters` ADD `title` varchar( 5 ) NULL AFTER `txnrefnum`;
ALTER TABLE `kotak_remit_remitters` ADD `gender` varchar( 10 ) NULL AFTER `title`;
ALTER TABLE `kotak_remit_remitters` ADD `landline` varchar( 20 ) NULL AFTER `gender`;
ALTER TABLE `kotak_remit_remitters` ADD `country` VARCHAR(5) NULL AFTER `landline`;
ALTER TABLE `kotak_remit_remitters` ADD `customer_type` enum('kyc','non-kyc') DEFAULT NULL;
ALTER TABLE `kotak_remit_remitters` ADD `by_api_user_id` int(11) NULL;

ALTER TABLE `kotak_beneficiaries` ADD `title` varchar( 5 ) NULL AFTER `remitter_id`;
ALTER TABLE `kotak_beneficiaries` ADD `bene_code` int(20) UNSIGNED NULL DEFAULT NULL;
ALTER TABLE `kotak_beneficiaries` ADD `txnrefnum` BIGINT( 16 ) NULL AFTER `bene_code`;

