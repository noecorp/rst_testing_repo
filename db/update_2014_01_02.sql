ALTER TABLE `kotak_corp_cardholders`
ADD COLUMN `recd_doc`  enum('yes','no') NOT NULL DEFAULT 'no' AFTER `date_authorize`,
ADD COLUMN `date_recd_doc`  date NULL AFTER `recd_doc`,
ADD COLUMN `recd_doc_id`  int(11) UNSIGNED NOT NULL AFTER `date_recd_doc`;

SET @flag_id := (SELECT id FROM `t_flags` WHERE name = 'bank-corp_kotak_customer');

Insert into `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'acceptdocument', @flag_id, 'Kotak Amul Customer Accept Physical document');


INSERT INTO `t_flags` (`name`, `description`, `active_on_dev`, `active_on_prod`) VALUES ('bank-corp_kotak_reports', 'Kotak Amul Reports', '1', '0');
SET @flag_id = last_insert_id();

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'index', @flag_id, 'Reports Index for Kotak Amul');
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'applications', @flag_id, 'Applications Report for Kotak Amul');
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'exportapplications', @flag_id, 'Applications Report for Kotak Amul');
