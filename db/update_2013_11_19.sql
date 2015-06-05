ALTER TABLE `rat_corp_cardholder_batch`
ADD COLUMN `failed_reason`  varchar(200) NULL AFTER `upload_status`;

ALTER TABLE `rat_corp_cardholder_batch`
MODIFY COLUMN `upload_status`  enum('temp','incomplete','pass','duplicate','rejected','failed') NOT NULL DEFAULT 'temp' AFTER `date_updated`;

UPDATE rat_corp_cardholder_batch SET upload_status = 'failed', failed_reason = 'Duplicate record' WHERE upload_status = 'duplicate' ;
