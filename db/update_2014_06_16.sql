ALTER TABLE `t_docs` ADD `doc_corporate_id` INT( 11 ) NOT NULL DEFAULT '0' AFTER `doc_boi_cust_id` ;
ALTER TABLE `t_docs` ADD `by_corporate_id` INT NOT NULL DEFAULT '0' AFTER `by_bank_id` ;

ALTER TABLE `kotak_corp_cardholders`
ADD COLUMN `customer_type`  enum('kyc','non-kyc') NULL DEFAULT NULL AFTER `customer_master_id`,
ADD COLUMN `by_corporate_id`  int(11) UNSIGNED NOT NULL AFTER `by_agent_id`;

CREATE TABLE `kotak_corp_cardholder_batch` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) unsigned NOT NULL,
  `card_number` varchar(16) NOT NULL,
  `card_pack_id` varchar(20) NOT NULL,
  `afn` varchar(10) NOT NULL,
  `member_id` varchar(10) NOT NULL,
  `employee_id` varchar(10) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `middle_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) NOT NULL,
  `name_on_card` varchar(100) NOT NULL,
  `gender` char(1) NOT NULL DEFAULT '',
  `date_of_birth` varchar(10) NOT NULL,
  `aadhaar_no` varchar(20) NOT NULL,
  `mobile` varchar(15) NOT NULL,
  `email` varchar(50) DEFAULT NULL,
  `landline` varchar(15) DEFAULT NULL,
  `address_line1` varchar(50) NOT NULL,
  `address_line2` varchar(50) DEFAULT NULL,
  `city` varchar(50) NOT NULL,
  `state` varchar(50) NOT NULL,
  `pincode` int(10) NOT NULL,
  `mother_maiden_name` varchar(25) NOT NULL,
  `employer_name` varchar(50) NOT NULL,
  `corporate_id` varchar(16) NOT NULL,
  `comm_address_line1` varchar(50) DEFAULT NULL,
  `comm_address_line2` varchar(50) NOT NULL,
  `comm_city` varchar(50) NOT NULL,
  `comm_pin` int(10) NOT NULL,
  `comm_state` varchar(50) NOT NULL,
  `id_proof_type` varchar(30) NOT NULL,
  `id_proof_number` varchar(50) NOT NULL,
  `id_proof_doc_id` int(11) unsigned NOT NULL,
  `address_proof_type` varchar(30) NOT NULL,
  `address_proof_number` varchar(50) NOT NULL,
  `address_proof_doc_id` int(11) unsigned NOT NULL,
  `photo_doc_id` int(11) unsigned NOT NULL,
  `other_id_proof` varchar(255) DEFAULT NULL,
  `by_ops_id` int(11) NOT NULL,
  `by_agent_id` int(11) unsigned NOT NULL,
  `by_corporate_id` int(11) unsigned NOT NULL,
  `batch_name` varchar(100) NOT NULL,
  `society_id` varchar(10) NOT NULL,
  `society_name` varchar(50) NOT NULL,
  `nominee_name` varchar(100) NOT NULL,
  `nominee_relationship` varchar(20) NOT NULL,
  `date_created` datetime NOT NULL,
  `place_application` varchar(100) NOT NULL,
  `date_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `upload_status` enum('temp','incomplete','pass','duplicate','rejected','failed') NOT NULL DEFAULT 'temp',
  `failed_reason` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

ALTER TABLE `kotak_corp_cardholders` ADD `pan` VARCHAR( 10 ) NOT NULL AFTER `aadhaar_no` ;

UPDATE rct_master SET ref_code = 'CHB' WHERE ref_rec_type = 01 and ref_code = '833';
UPDATE rct_master SET ref_code = 'RAIPU' WHERE ref_rec_type = 01 and ref_code = '492';
UPDATE rct_master SET ref_code = 'CHIN1' WHERE ref_rec_type = 01 and ref_code = '1HIN';
UPDATE rct_master SET ref_code = 'GURU' WHERE ref_rec_type = 01 and ref_code = '01';
UPDATE rct_master SET ref_code = 'BALLI' WHERE ref_rec_type = 01 and ref_code = '277';
UPDATE rct_master SET ref_code = 'CHITR' WHERE ref_rec_type = 01 and ref_code = '856';

UPDATE boi_corp_cardholders SET city = 'GURU' WHERE city = '01' ;
UPDATE boi_corp_cardholders SET city = 'BALLI' WHERE city = '277' ;
UPDATE boi_corp_cardholders SET city = 'RAIPU' WHERE city = '492' ;
UPDATE boi_corp_cardholders SET city = 'CHITR' WHERE city = '856' ;

UPDATE boi_corp_cardholders SET comm_city = 'GURU' WHERE comm_city = '01' ;
UPDATE boi_corp_cardholders SET comm_city = 'BALLI' WHERE comm_city = '277';
UPDATE boi_corp_cardholders SET comm_city = 'RAIPU' WHERE comm_city = '492' ;
UPDATE boi_corp_cardholders SET comm_city = 'CHB' WHERE comm_city = '833' ;
UPDATE boi_corp_cardholders SET comm_city = 'CHITR' WHERE comm_city = '856' ;