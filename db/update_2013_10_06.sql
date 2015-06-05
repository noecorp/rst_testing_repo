ALTER TABLE `bank_users` CHANGE `is_logged` `is_logged` BOOLEAN NOT NULL DEFAULT FALSE ;

ALTER TABLE `bank_users` CHANGE `password` `password` VARCHAR( 128 ) NOT NULL; 
