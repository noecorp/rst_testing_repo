ALTER TABLE `rat_corp_insurance_claim` CHANGE `status` `status` ENUM( 'pending', 'loaded', 'failed', 'cutoff', 'blocked', 'completed', 'incomplete' ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT 'pending';

ALTER TABLE `rat_corp_insurance_claim`
ADD COLUMN `rr_no`  varchar(15) NULL DEFAULT NULL AFTER `hospital_mcc`;
