ALTER TABLE `boi_corp_cardholders` CHANGE `debit_mandate_accout` `debit_mandate_account` VARCHAR( 50 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL;

DELETE FROM `t_product_privileges` WHERE `product_id` =7 AND `flag_id` =29;
DELETE FROM `t_product_privileges` WHERE `product_id` =7 AND `flag_id` =30;
DELETE FROM `t_product_privileges` WHERE `product_id` =7 AND `flag_id` =31;
DELETE FROM `t_product_privileges` WHERE `product_id` =7 AND `flag_id` =28;


DELETE FROM `t_product_privileges` WHERE `product_id` =7 AND `flag_id` =28 AND privilege_id =146;
DELETE FROM `t_product_privileges` WHERE `product_id` =7 AND `flag_id` =33 AND privilege_id =154;
DELETE FROM `t_product_privileges` WHERE `product_id` =7 AND `flag_id` =33 AND privilege_id =155;
DELETE FROM `t_product_privileges` WHERE `product_id` =7 AND `flag_id` =33 AND privilege_id =203;
DELETE FROM `t_product_privileges` WHERE `product_id` =7 AND `flag_id` =33 AND privilege_id =204;
DELETE FROM `t_product_privileges` WHERE `product_id` =7 AND `flag_id` =33 AND privilege_id =224;
DELETE FROM `t_product_privileges` WHERE `product_id` =7 AND `flag_id` =33 AND privilege_id =225;
DELETE FROM `t_product_privileges` WHERE `product_id` =7 AND `flag_id` =33 AND privilege_id =228;

