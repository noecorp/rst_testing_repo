ALTER TABLE `t_change_status_log` ADD `beneficiary_id` INT( 11 ) UNSIGNED NOT NULL AFTER `bank_id`;


SET @flag_id := (SELECT id FROM `t_flags` where name ='agent-remit_boi_beneficiary');
INSERT INTO `t_privileges` (`name`, `flag_id`, `description`) VALUES ('deactivatebeneficiary',@flag_id , 'Deactivate Beneficiary');
SET @product_id := (SELECT id from t_products WHERE program_type = 'Remit'); 
SET @priv_id := (SELECT id FROM t_privileges WHERE name = 'deactivatebeneficiary');
INSERT INTO `t_product_privileges` (`product_id`, `flag_id`, `privilege_id`, `allow`) VALUES
(@product_id, @flag_id, @priv_id, 1);

INSERT INTO `t_flags` (`id`, `name`, `description`, `active_on_dev`, `active_on_prod`) VALUES (NULL, 'operation-corporate', 'Manage Corporates', '1', '0');
SET @flag_id = last_insert_id();
INSERT INTO `t_privileges` (`name`, `flag_id`, `description`) VALUES ('index',@flag_id , 'Corporates Listing');
INSERT INTO `t_privileges` (`name`, `flag_id`, `description`) VALUES ('add',@flag_id , 'Add Corporates');
INSERT INTO `t_privileges` (`name`, `flag_id`, `description`) VALUES ('edit',@flag_id , 'Edit Corporates');
INSERT INTO `t_privileges` (`name`, `flag_id`, `description`) VALUES ('delete',@flag_id , 'Delete Corporates');