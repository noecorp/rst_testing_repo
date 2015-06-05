ALTER TABLE `boi_corp_cardholders`
MODIFY COLUMN `country_code`  varchar(5)  NOT NULL DEFAULT 'IN' AFTER `pincode`,
MODIFY COLUMN `comm_country_code`  varchar(5)  NOT NULL DEFAULT 'IN' AFTER `comm_state`,
MODIFY COLUMN `cust_comm_code`  char(1)  NOT NULL DEFAULT 'C' AFTER `marital_status`,
MODIFY COLUMN `schm_code`  varchar(20)  NOT NULL DEFAULT 'SB101' AFTER `account_id_ver_flg`,
MODIFY COLUMN `orgaization_type`  varchar(5) NOT NULL DEFAULT 'INDIV' AFTER `schm_code`;

