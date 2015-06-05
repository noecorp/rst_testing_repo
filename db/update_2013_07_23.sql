update t_flags set name='agent-corp_ratnakar_cardholder' where name='agent-hic_ratnakar_cardholder' limit 1;
update t_flags set name='agent-corp_ratnakar_hospital' where name='agent-hic_ratnakar_hospital' limit 1;
update t_flags set name='operation-corp_ratnakar_cardholder' where name='operation-hic_ratnakar_cardholder' limit 1;
update t_flags set name='agent-corp_ratnakar_cardload' where name='agent-hic_ratnakar_cardload' limit 1;
ALTER TABLE  `rat_hic_cardholders` ADD  `aadhaar_no` VARCHAR( 20 ) NULL DEFAULT NULL AFTER  `last_name` ,
ADD  `pan` VARCHAR( 10 ) NULL DEFAULT NULL AFTER  `aadhaar_no`;
