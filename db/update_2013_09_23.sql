INSERT INTO `t_flags` (`id`, `name`,`description`) VALUES (NULL,'operation-agentfunding','Agent Funding');
SET @flag_id = LAST_INSERT_ID();
Insert into `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'index', @flag_id, 'Operation Agent Funding Index');

