ALTER TABLE `t_unicode` CHANGE `crn` `crn` VARCHAR(40) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL ;
ALTER TABLE `t_ecs_crn` CHANGE `crn` `crn` VARCHAR(40) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL ;
ALTER TABLE `t_cardholder_details` CHANGE `crn` `crn` VARCHAR(40) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL ; 
ALTER TABLE `kotak_batch_adjustment` CHANGE `card_number` `card_number` VARCHAR(40) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL ;
ALTER TABLE `boi_corp_load_request_batch` CHANGE `card_number` `card_number` VARCHAR(40) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL ;
ALTER TABLE `boi_corp_load_request` CHANGE `card_number` `card_number` VARCHAR(40) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL ;

ALTER TABLE `boi_corp_cardholders_details` CHANGE `card_number` `card_number` VARCHAR(40) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL ;
ALTER TABLE `boi_corp_cardholders_details` CHANGE `crn` `crn` VARCHAR(40) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL ;



update `t_unicode` set crn = AES_ENCRYPT(crn,'goprs010058074ea3dc0bc89ge8aprcf');
update `t_ecs_crn` set crn = AES_ENCRYPT(crn,'goprs010058074ea3dc0bc89ge8aprcf');
update `t_cardholder_details` set crn = AES_ENCRYPT(crn,'goprs010058074ea3dc0bc89ge8aprcf'); 
update `kotak_batch_adjustment` set card_number = AES_ENCRYPT(card_number,'goprs010058074ea3dc0bc89ge8aprcf');
update `boi_corp_load_request_batch` set card_number = AES_ENCRYPT(card_number,'goprs010058074ea3dc0bc89ge8aprcf');
update `rat_corp_load_request_batch` set card_number = AES_ENCRYPT(card_number,'goprs010058074ea3dc0bc89ge8aprcf');
update `boi_corp_load_request` set card_number = AES_ENCRYPT(card_number,'goprs010058074ea3dc0bc89ge8aprcf');
update `boi_corp_cardholders_details` set card_number = AES_ENCRYPT(card_number,'goprs010058074ea3dc0bc89ge8aprcf');
update `boi_corp_cardholders_details` set crn = AES_ENCRYPT(crn,'goprs010058074ea3dc0bc89ge8aprcf');









