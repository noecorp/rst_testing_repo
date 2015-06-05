CREATE TABLE t_customer_update_log LIKE customers_detail;
ALTER TABLE `t_customer_update_log` ADD `mobile_country_code` VARCHAR( 6 ) NULL DEFAULT NULL AFTER `bank_id` ,
ADD `old_mobile` VARCHAR( 15 ) NOT NULL AFTER `mobile_country_code` ,
ADD `new_mobile` VARCHAR( 15 ) NOT NULL AFTER `old_mobile` ;