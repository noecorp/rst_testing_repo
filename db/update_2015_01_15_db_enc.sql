ALTER TABLE `kotak_corp_cardholders` CHANGE `crn` `crn` VARCHAR(35) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL, CHANGE `card_number` `card_number` VARCHAR(35) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL;

ALTER TABLE `rat_corp_cardholders` CHANGE `crn` `crn` VARCHAR(35) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL, CHANGE `card_number` `card_number` VARCHAR(35) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL;