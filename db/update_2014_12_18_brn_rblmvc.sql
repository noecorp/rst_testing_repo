ALTER TABLE `rat_mvc_cardholder_details` ADD `product_id` INT( 11 ) UNSIGNED NOT NULL AFTER `id`;
ALTER TABLE `rat_mvc_cardholder_details` ADD `landline` VARCHAR( 15 ) NOT NULL AFTER `flat_number`;
ALTER TABLE `rat_mvc_cardholder_details` CHANGE `already_bank_account` `already_bank_account` ENUM( 'y', 'n' ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;
