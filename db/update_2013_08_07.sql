ALTER TABLE  `t_agent_closing_balance` ADD  `date_updated` TIMESTAMP NOT NULL;

UPDATE t_beneficiaries SET bank_account_number=AES_ENCRYPT(bank_account_number, 'put_key_here'), branch_address=AES_ENCRYPT(branch_address, 'put_key_here'), mobile=AES_ENCRYPT(mobile, 'put_key_here'), email=AES_ENCRYPT(email, 'put_key_here');

ALTER TABLE `rat_corp_cardholders`
DROP COLUMN `upload_status`,
MODIFY COLUMN `status`  enum('active','inactive','ecs_pending','ecs_failed') CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT 'ecs_pending' ,
ADD COLUMN `date_failed`  timestamp NULL AFTER `date_updated`,
ADD COLUMN `failed_reason`  varchar(200) NULL AFTER `status`;

CREATE TABLE `rat_corp_cardholders_batch` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `card_number` varchar(10) NOT NULL,
  `afn` varchar(10) NOT NULL,
  `medi_assist_id` varchar(10) NOT NULL,
  `employee_id` varchar(10) NOT NULL,
  `first_name` varchar(26) NOT NULL,
  `middle_name` varchar(26) NOT NULL,
  `last_name` varchar(26) NOT NULL,
  `aadhaar_no` varchar(20) DEFAULT NULL,
  `pan` varchar(10) DEFAULT NULL,
  `gender` enum('male','female') NOT NULL,
  `date_of_birth` date NOT NULL,
  `mobile` varchar(15) NOT NULL,
  `email` varchar(100) NOT NULL,
  `employer_name` varchar(100) NOT NULL,
  `corporate_id` varchar(11) NOT NULL,
  `batch_name` varchar(100) NOT NULL,
  `by_ops_id` int(11) unsigned NOT NULL,
  `date_created` timestamp NULL DEFAULT NULL,
  `date_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `upload_status` enum('temp','incomplete','pass','duplicate') NOT NULL DEFAULT 'temp',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

