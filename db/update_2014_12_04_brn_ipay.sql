


/************ 1 -- Add New Bank ICICI ****************/
INSERT INTO `t_bank` (`id`, `name`, `ifsc_code`, `city`, `branch_name`, `address`, `unicode`, `logo`, `by_ops_id`, `ip`, `date_created`, `status`) VALUES
('', 'THE ICICI BANK LTD', 'ICIC0000011', 'MUMBAI', 'MUMBAI - ANDHERI', 'SAGAR AVENUE, GROUND FLOOR, OPP. SHOPPERS STOP, S.V. ROAD, ANDHERI (W), MUMBAI. 400058', 500, NULL, 101, 127000000001, '2014-10-15 02:22:07', 'active');
 



/************ 2 -- Add New cron File ****************/
INSERT INTO `t_cron` (`id`, `name`, `description`, `file_name`, `status`, `status_cron`, `date_updated`) VALUES (52, 'Agent Funding for ICICI', 'Agent Funding for IPay ', 'AgentFundingIPay.php', 'active', 'completed', '2014-10-15 09:46:58');

ALTER TABLE `agent_funding` CHANGE `settlement_by` `settlement_by` ENUM( 'system', 'ops', 'api' ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL;





/************ 3 -- Add New ipay_bank_statment table  ****************/
 
DROP TABLE IF EXISTS `ipay_bank_statment`;
CREATE TABLE IF NOT EXISTS `ipay_bank_statment` (
`id` int(11) NOT NULL,
  `bank_statment_id` int(11) NOT NULL,
  `customer_code` varchar(50) NOT NULL,
  `vendor_code` varchar(50) NOT NULL,
  `amount` decimal(11,2) NOT NULL,
  `transaction_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `utr_no` varchar(50) NOT NULL,
  `ifsc_code` varchar(20) NOT NULL,
  `credit_account_no` varchar(35) NOT NULL,
  `vendor_name` varchar(150) NOT NULL,
  `payment_mode` varchar(50) NOT NULL,
  `beneficiary_bank` varchar(100) NOT NULL
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
 
ALTER TABLE `ipay_bank_statment`
 ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `bank_statment_id` (`bank_statment_id`);
 
ALTER TABLE `ipay_bank_statment`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=1;


/************ 4 -- Add New agent_funding_ipay table  ****************/
 CREATE TABLE IF NOT EXISTS `agent_funding_ipay` (
`id` int(11) NOT NULL AUTO_INCREMENT,
  `agent_id` int(11) NOT NULL,
  `agent_mobile` varchar(15) NOT NULL,
  `amount` decimal(11,2) NOT NULL,
  `bank_transaction_id` varchar(15) NOT NULL,
  `pay_mode` enum('C','F','L') NOT NULL,
  `isure_id` varchar(11) NOT NULL,
  `micro_code` varchar(15) NOT NULL,
  `bank_name` varchar(100) NOT NULL,
  `branch_name` varchar(100) NOT NULL,
  `instrument_number` varchar(20) NOT NULL,
  `instrument_date` date NOT NULL,
  `transaction_date` date NOT NULL,
  `status` enum('success','pending') NOT NULL DEFAULT 'pending',
  `request_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `settlement_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;



/************ 5 -- Add New Api User  ****************/
 INSERT INTO `api_user` (`id`, `tp_user_id`, `username`, `password`, `status`, `date_created`) VALUES ('29', '29', 'tsvicici', '34rf3a4557510c82d47d0ac1a5244', 'active', '0000-00-00 00:00:00');









