ALTER TABLE `purse_master` ADD `allow_expiry` ENUM( 'yes', 'no' ) NOT NULL DEFAULT 'no' AFTER `allow_mvc` ;
UPDATE purse_master SET allow_expiry = 'yes' WHERE code IN ('BPR923','SGP924');
UPDATE purse_master SET allow_remit = 'no' WHERE code IN ('SGP924');