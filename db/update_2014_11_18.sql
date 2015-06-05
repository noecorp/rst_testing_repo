ALTER TABLE `kotak_remittance_request` ADD `otp` VARCHAR(20) NOT NULL AFTER `is_complete`, ADD `date_otp` DATETIME NULL AFTER `is_complete`; 

UPDATE `t_products` SET `const` = 'KOTAK_REMIT', `static_otp` = 'yes' WHERE `t_products`.`unicode` = '410';

ALTER TABLE `kotak_remit_remitters` DROP `static_code`;

ALTER TABLE `kotak_remit_remitters` ADD `otp` VARCHAR(20) NOT NULL AFTER `ip`, ADD `date_otp` DATETIME NULL AFTER `ip`; 

ALTER TABLE `t_products` ADD `static_otp` ENUM( 'yes', 'no' ) NOT NULL DEFAULT 'no' AFTER `flag_common`;
