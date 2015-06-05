ALTER TABLE `t_email_verification` ADD `agent_detail_id` INT( 10 ) UNSIGNED NOT NULL AFTER `agent_id`;
ALTER TABLE `t_agent_details` ADD `auth_email_verification_id` INT( 11 ) UNSIGNED NOT NULL AFTER `by_ops_id` ,
ADD `auth_email_verification_status` ENUM( 'pending', 'verified' ) NOT NULL DEFAULT 'pending' AFTER `auth_email_verification_id`;
INSERT INTO `t_flags` (`id`, `name`, `description`, `active_on_dev`, `active_on_prod`) VALUES (NULL, 'agent-authemailauthorization', 'Agent Auth Email authorization', '1', '0');
SET @flag_id := last_insert_id();
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'index', @flag_id, 'Agent Auth Email authorization');
