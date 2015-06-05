ALTER TABLE `boi_delivery_file_master` CHANGE `gender` `gender` VARCHAR( 10 ) NOT NULL DEFAULT 'male',
CHANGE `date_created` `date_created` VARCHAR( 15 ) NOT NULL;

ALTER TABLE `boi_delivery_file_master` CHANGE `passport_issue_date` `passport_issue_date` VARCHAR( 15 ) NOT NULL ,
CHANGE `passport_expiry_date` `passport_expiry_date` VARCHAR( 15 ) NOT NULL ,
CHANGE `nominee_dob` `nominee_dob` VARCHAR( 15 ) NOT NULL;