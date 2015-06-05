ALTER TABLE `rat_corp_cardholder_batch` CHANGE `gender` `gender` VARCHAR( 10 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL;
ALTER TABLE `rat_corp_cardholder_batch` CHANGE `upload_status` `upload_status` ENUM( 'temp', 'incomplete', 'pass', 'duplicate', 'rejected' ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT 'temp'

SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-hic_ratnakar_cardholder');
UPDATE `t_privileges` SET `name` = 'searchcardholder' WHERE name = 'searchcardholders' AND  `flag_id` = @flag_id LIMIT 1;

