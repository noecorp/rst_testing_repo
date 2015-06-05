ALTER TABLE `rat_corp_cardholders` CHANGE `crn` `crn` VARCHAR(40) NOT NULL, CHANGE `card_number` `card_number` VARCHAR(40) NOT NULL; 
ALTER TABLE `crn_master` CHANGE `card_number` `card_number` VARCHAR(40) NOT NULL; 
ALTER TABLE `rat_corp_cardholder_batch` CHANGE `card_number` `card_number` VARCHAR(40) NOT NULL;
ALTER TABLE `rat_corp_load_request` CHANGE `card_number` `card_number` VARCHAR(40)  NOT NULL;
ALTER TABLE `rat_corp_load_request_batch` CHANGE `card_number` `card_number` VARCHAR(40) NOT NULL;
ALTER TABLE `card_txn_processing` CHANGE `card_number` `card_number` VARCHAR(40) NOT NULL;
ALTER TABLE `t_batch_adjustment` CHANGE `card_number` `card_number` VARCHAR(40) NOT NULL;


ALTER TABLE `boi_corp_cardholders` CHANGE `crn` `crn` VARCHAR(40) NOT NULL, CHANGE `card_number` `card_number` VARCHAR(40) NOT NULL; 
ALTER TABLE `t_cardholders` CHANGE COLUMN `crn` `crn` VARCHAR(40) NULL DEFAULT NULL COLLATE 'utf8_general_ci' AFTER `approval_datetime`; 
ALTER TABLE `boi_delivery_file_master` CHANGE COLUMN `card_number` `card_number` VARCHAR(40);
ALTER TABLE `boi_card_mapping` CHANGE COLUMN `card_number` `card_number` VARCHAR(40);


ALTER TABLE `kotak_corp_load_request` CHANGE `card_number` `card_number` VARCHAR(40) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL ;
ALTER TABLE `kotak_corp_load_request_batch` CHANGE COLUMN `card_number` `card_number` VARCHAR(40) NOT NULL;
ALTER TABLE `kotak_corp_cardholders` CHANGE `crn` `crn` VARCHAR(40) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL, CHANGE `card_number` `card_number` VARCHAR(40) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL;
ALTER TABLE `kotak_corp_cardholders_details` CHANGE `crn` `crn` VARCHAR(40) NOT NULL, CHANGE `card_number` `card_number` VARCHAR(40) NOT NULL; 
ALTER TABLE `delivery_file_master` CHANGE `card_number` `card_number` VARCHAR(40) NOT NULL;
ALTER TABLE `kotak_corp_cardholder_batch` CHANGE `card_number` `card_number` VARCHAR(40) NOT NULL; 


update `crn_master` set card_number = AES_ENCRYPT(card_number,'goprs010058074ea3dc0bc89ge8aprcf');
update `rat_corp_cardholders` set card_number = AES_ENCRYPT(card_number,'goprs010058074ea3dc0bc89ge8aprcf');
update `rat_corp_cardholders` set crn = AES_ENCRYPT(crn,'goprs010058074ea3dc0bc89ge8aprcf');

update `rat_corp_cardholder_batch` set card_number = AES_ENCRYPT(card_number,'goprs010058074ea3dc0bc89ge8aprcf');
update `rat_corp_load_request` set card_number = AES_ENCRYPT(card_number,'goprs010058074ea3dc0bc89ge8aprcf');
update `card_txn_processing` set card_number = AES_ENCRYPT(card_number,'goprs010058074ea3dc0bc89ge8aprcf');

update `t_batch_adjustment` set card_number = AES_ENCRYPT(card_number,'goprs010058074ea3dc0bc89ge8aprcf');

update `boi_corp_cardholders` set card_number = AES_ENCRYPT(card_number,'goprs010058074ea3dc0bc89ge8aprcf');
update `boi_corp_cardholders` set crn = AES_ENCRYPT(crn,'goprs010058074ea3dc0bc89ge8aprcf');

update `t_cardholders` set crn = AES_ENCRYPT(crn,'goprs010058074ea3dc0bc89ge8aprcf');


update `boi_delivery_file_master` set card_number = AES_ENCRYPT(card_number,'goprs010058074ea3dc0bc89ge8aprcf');
update `boi_card_mapping` set card_number = AES_ENCRYPT(card_number,'goprs010058074ea3dc0bc89ge8aprcf');



update `kotak_corp_load_request` set card_number = AES_ENCRYPT(card_number,'goprs010058074ea3dc0bc89ge8aprcf');
update `kotak_corp_cardholder_batch` set card_number = AES_ENCRYPT(card_number,'goprs010058074ea3dc0bc89ge8aprcf');

update `delivery_file_master` set card_number = AES_ENCRYPT(card_number,'goprs010058074ea3dc0bc89ge8aprcf');
update `delivery_file_master` set card_number = AES_ENCRYPT(card_number,'goprs010058074ea3dc0bc89ge8aprcf');

update `kotak_corp_cardholders` set crn = AES_ENCRYPT(crn,'goprs010058074ea3dc0bc89ge8aprcf');
update `kotak_corp_cardholders_details` set crn = AES_ENCRYPT(crn,'goprs010058074ea3dc0bc89ge8aprcf');

update `customer_track` set info = AES_ENCRYPT(info,'goprs010058074ea3dc0bc89ge8aprcf') WHERE flag = 5 OR flag = 7;

ALTER TABLE `api_soap_calls` CHANGE `request` `request` TEXT CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL ;
ALTER TABLE `api_soap_calls` CHANGE `response` `response` TEXT CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL ;

