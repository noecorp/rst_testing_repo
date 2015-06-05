ALTER TABLE `rat_corp_load_request_batch`
MODIFY COLUMN `amount`  varchar(10) NOT NULL AFTER `medi_assist_id`;

ALTER TABLE `rat_corp_load_request_batch`
MODIFY COLUMN `upload_status`  enum('temp','incomplete','pass','duplicate','rejected','failed') NOT NULL DEFAULT 'temp' ,
ADD COLUMN `failed_reason`  varchar(200) NOT NULL AFTER `date_updated`;


ALTER TABLE `rat_corp_load_request_batch`
MODIFY COLUMN `wallet_code`  varchar(10) NOT NULL DEFAULT '' AFTER `narration`;

ALTER TABLE `rat_corp_load_request`
MODIFY COLUMN `wallet_code`  varchar(10) NOT NULL DEFAULT '' AFTER `narration`;

