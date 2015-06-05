CREATE TABLE IF NOT EXISTS `log_hic_hospital` (
  `hospital_id` int(11) unsigned NOT NULL,
  `hospital_id_code` int(11) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `address` varchar(255) NOT NULL,
  `city` varchar(50) DEFAULT NULL,
  `state` varchar(50) DEFAULT NULL,
  `pincode` int(11) unsigned DEFAULT NULL,
  `std_code` varchar(10) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `status` enum('active','inactive','deleted') DEFAULT 'active',
  `by_agent_id` int(11) unsigned NOT NULL,
  `ip` varchar(16) DEFAULT NULL,
  `date_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `log_hic_terminal`
--

CREATE TABLE IF NOT EXISTS `log_hic_terminal` (
  `terminal_id` int(11) unsigned NOT NULL,
  `hospital_id` int(11) unsigned NOT NULL,
  `terminal_id_code` bigint(16) unsigned NOT NULL,
  `status` enum('active','inactive','deleted') NOT NULL DEFAULT 'active',
  `by_agent_id` int(11) DEFAULT NULL,
  `ip` varchar(16) DEFAULT NULL,
  `date_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



SET @flag_id := (SELECT id FROM `t_flags` WHERE name='agent-hic_ratnakar_hospital');
SET @product_id := (SELECT id FROM `t_products` WHERE name='MEDI ASSIST CARD');

INSERT INTO `t_privileges` (`name`, `flag_id`, `description`) VALUES ('delete', @flag_id, 'Delete Hospital');
SET @privilege_id = last_insert_id();
INSERT INTO `t_product_privileges` (`product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (@product_id, @flag_id, @privilege_id, 1);
