INSERT INTO `t_flags` (`id`, `name`, `description`, `active_on_dev`, `active_on_prod`) VALUES (NULL, 'customer-profile', 'Customer Profile ', 1, 0);
SET @flag_id :=  last_insert_id();

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'index', @flag_id, 'Allow users to see their dashboards');
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'login', @flag_id, 'Login: 1st step (Allow Operation users to log into the application)');
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'authcode', @flag_id, 'Login: 2nd step (Operations Authcode)');
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'edit', @flag_id, 'Allow users to update their profiles');
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'change-password', @flag_id, 'Allow users to change their passwords');
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'logout', @flag_id, 'Allows users to log out of the application');
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'resend-authcode', @flag_id, 'Resend Authcode on Operation Portal');
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'forgot-password', @flag_id, 'Forgot Password: 1st step (Forgot Password without login in Operation Portal)');
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'confirmation-code', @flag_id, 'Forgot Password: 2nd step (Confirmation Code in Operation Portal)');
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'new-password', @flag_id, 'Forgot Password: 3rd step (Create new Password in Operation Portal)');
INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'wallets', @flag_id, 'Dispay Wallets');


-- INSERT INTO `t_product_privileges` (`product_id`, `flag_id`, `privilege_id`, `allow`) VALUES ('4', '34', '89', '1');