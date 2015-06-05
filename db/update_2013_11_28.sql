ALTER TABLE `kotak_corp_cardholders` CHANGE `status_bank` `status_bank` ENUM( 'pending', 'approved', 'rejected' ) NOT NULL DEFAULT 'pending',
CHANGE `status_ops` `status_ops` ENUM( 'pending', 'approved', 'rejected' ) NOT NULL DEFAULT 'pending',
CHANGE `status_ecs` `status_ecs` ENUM( 'pending', 'failure', 'success' ) NOT NULL DEFAULT 'pending';

INSERT INTO `t_flags` (`name`, `description`, `active_on_dev`, `active_on_prod`) VALUES ('bank-corp_kotak_customer', 'Kotak Amul Cardholders', '1', '0');
SET @flag_id_val = last_insert_id();

Insert into `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'index', @flag_id_val, 'Kotak Amul Index page');

Insert into `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'search', @flag_id_val, 'Search Customer');

Insert into `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'approve', @flag_id_val, 'Approve Customer');

Insert into `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'reject', @flag_id_val, 'Reject Customer');
