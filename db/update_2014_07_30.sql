
UPDATE `shmart`.`t_bank` SET `logo` = 'logo-ratnakar.png' WHERE `t_bank`.`id` = 3;


-- Ratnakar Remittance
    -- Operation Helpdesk
	-- Index
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-remit_ratnakar_remitter'); 
	SET @ops_id = '4';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='index' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

	-- Search
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-remit_ratnakar_remitter'); 
	SET @ops_id = '4';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='search' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-remit_ratnakar_remitter'); 
	SET @ops_id = '4';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='beneficiary' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

	-- UTR Payment History
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-remit_ratnakar_remitter'); 
	SET @ops_id = '4';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='uploadpaymenthistory' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

	-- Final Response File
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-remit_ratnakar_remitter'); 
	SET @ops_id = '4';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='uploadresponsepaymenthistory' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

	-- Remittance Report
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-remit_ratnakar_remitter'); 
	SET @ops_id = '4';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='searchreport' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-remit_ratnakar_remitter'); 
	SET @ops_id = '4';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='exportsearchreport' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');




    -- Operation (Maker)
	-- Index
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-remit_ratnakar_remitter'); 
	SET @ops_id = '8';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='index' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

	-- Search
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-remit_ratnakar_remitter'); 
	SET @ops_id = '8';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='search' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-remit_ratnakar_remitter'); 
	SET @ops_id = '8';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='beneficiary' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

	-- UTR Payment History
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-remit_ratnakar_remitter'); 
	SET @ops_id = '8';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='uploadpaymenthistory' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

	-- Final Response File
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-remit_ratnakar_remitter'); 
	SET @ops_id = '8';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='uploadresponsepaymenthistory' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

	-- NEFT Instruction Batches
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-remit_ratnakar_remitter'); 
	SET @ops_id = '8';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='neftrequests' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-remit_ratnakar_remitter'); 
	SET @ops_id = '8';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='neftbatchdetails' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-remit_ratnakar_remitter'); 
	SET @ops_id = '8';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='neftbatch' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-remit_ratnakar_remitter'); 
	SET @ops_id = '8';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='neftprocessed' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

	-- Manual Mapping
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-remit_ratnakar_remitter'); 
	SET @ops_id = '8';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='manualmapping' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-remit_ratnakar_remitter'); 
	SET @ops_id = '8';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='manualmappingupdate' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

	-- Remittance Report
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-remit_ratnakar_remitter'); 
	SET @ops_id = '8';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='searchreport' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-remit_ratnakar_remitter'); 
	SET @ops_id = '8';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='exportsearchreport' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');




    -- Operation (Checker)
	-- Index
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-remit_ratnakar_remitter'); 
	SET @ops_id = '9';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='index' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

	-- Search
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-remit_ratnakar_remitter'); 
	SET @ops_id = '9';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='search' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-remit_ratnakar_remitter'); 
	SET @ops_id = '9';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='beneficiary' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

	-- UTR Payment History
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-remit_ratnakar_remitter'); 
	SET @ops_id = '9';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='uploadpaymenthistory' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

	-- Final Response File
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-remit_ratnakar_remitter'); 
	SET @ops_id = '9';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='uploadresponsepaymenthistory' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

	-- NEFT Instruction Batches
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-remit_ratnakar_remitter'); 
	SET @ops_id = '9';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='neftrequests' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-remit_ratnakar_remitter'); 
	SET @ops_id = '9';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='neftbatchdetails' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-remit_ratnakar_remitter'); 
	SET @ops_id = '9';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='neftbatch' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-remit_ratnakar_remitter'); 
	SET @ops_id = '9';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='neftprocessed' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

	-- Manual Mapping
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-remit_ratnakar_remitter'); 
	SET @ops_id = '9';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='manualmapping' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-remit_ratnakar_remitter'); 
	SET @ops_id = '9';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='manualmappingupdate' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

	-- Remittance Report
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-remit_ratnakar_remitter'); 
	SET @ops_id = '9';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='searchreport' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-remit_ratnakar_remitter'); 
	SET @ops_id = '9';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='exportsearchreport' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');




    -- Product
	-- Index
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-remit_ratnakar_remitter'); 
	SET @ops_id = '5';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='index' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

	-- Search
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-remit_ratnakar_remitter'); 
	SET @ops_id = '5';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='search' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-remit_ratnakar_remitter'); 
	SET @ops_id = '5';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='beneficiary' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

	-- Remittance Report
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-remit_ratnakar_remitter'); 
	SET @ops_id = '5';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='searchreport' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-remit_ratnakar_remitter'); 
	SET @ops_id = '5';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='exportsearchreport' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');


    -- Accounts
	-- Index
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-remit_ratnakar_remitter'); 
	SET @ops_id = '6';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='index' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

	-- Search
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-remit_ratnakar_remitter'); 
	SET @ops_id = '6';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='search' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-remit_ratnakar_remitter'); 
	SET @ops_id = '6';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='beneficiary' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

	-- NEFT Instruction Batches
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-remit_ratnakar_remitter'); 
	SET @ops_id = '6';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='neftrequests' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-remit_ratnakar_remitter'); 
	SET @ops_id = '6';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='neftbatchdetails' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-remit_ratnakar_remitter'); 
	SET @ops_id = '6';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='neftbatch' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-remit_ratnakar_remitter'); 
	SET @ops_id = '6';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='neftprocessed' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

	-- Remittance Report
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-remit_ratnakar_remitter'); 
	SET @ops_id = '6';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='searchreport' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-remit_ratnakar_remitter'); 
	SET @ops_id = '6';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='exportsearchreport' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');




    -- Sales
	-- Index
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-remit_ratnakar_remitter'); 
	SET @ops_id = '7';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='index' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

	-- Search
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-remit_ratnakar_remitter'); 
	SET @ops_id = '7';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='search' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-remit_ratnakar_remitter'); 
	SET @ops_id = '7';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='beneficiary' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

	-- Remittance Report
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-remit_ratnakar_remitter'); 
	SET @ops_id = '7';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='searchreport' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-remit_ratnakar_remitter'); 
	SET @ops_id = '7';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='exportsearchreport' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

-- Agents
    -- Operation Helpdesk
	-- Index
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-agents'); 
	SET @ops_id = '4';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='index' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');
        
        --Agents
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-agentsummary'); 
	SET @ops_id = '4';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='index' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-agents'); 
	SET @ops_id = '4';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='edit' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-agentsummary'); 
	SET @ops_id = '4';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='block' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-agentsummary'); 
	SET @ops_id = '4';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='unblock' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-agentsummary'); 
	SET @ops_id = '4';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='unlock' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-agentsummary'); 
	SET @ops_id = '4';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='view' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

        -- Approval Pending
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-approveagent'); 
	SET @ops_id = '4';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='index' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-approveagent'); 
	SET @ops_id = '4';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='approve' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-approveagent'); 
	SET @ops_id = '4';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='reject' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-approveagent'); 
	SET @ops_id = '4';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='view' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');


    -- Operation (Maker)
	-- Index
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-agents'); 
	SET @ops_id = '8';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='index' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');
       
        --Agents
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-agentsummary'); 
	SET @ops_id = '8';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='index' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-agents'); 
	SET @ops_id = '8';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='edit' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-agentsummary'); 
	SET @ops_id = '8';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='block' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-agentsummary'); 
	SET @ops_id = '8';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='unblock' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-agentsummary'); 
	SET @ops_id = '8';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='unlock' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-agentsummary'); 
	SET @ops_id = '8';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='view' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

        -- Approval Pending
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-approveagent'); 
	SET @ops_id = '8';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='index' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-approveagent'); 
	SET @ops_id = '8';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='approve' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-approveagent'); 
	SET @ops_id = '8';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='reject' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-approveagent'); 
	SET @ops_id = '8';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='view' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

        -- Rejecetd Agents
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-approveagent'); 
	SET @ops_id = '8';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='rejectedlist' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');


    -- Operation (Checker)
	-- Index
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-agents'); 
	SET @ops_id = '9';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='index' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');
       
        --Agents
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-agentsummary'); 
	SET @ops_id = '9';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='index' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-agents'); 
	SET @ops_id = '9';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='edit' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-agentsummary'); 
	SET @ops_id = '9';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='block' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-agentsummary'); 
	SET @ops_id = '9';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='unblock' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-agentsummary'); 
	SET @ops_id = '9';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='unlock' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-agentsummary'); 
	SET @ops_id = '9';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='view' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

        -- Approval Pending
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-approveagent'); 
	SET @ops_id = '9';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='index' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-approveagent'); 
	SET @ops_id = '9';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='approve' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-approveagent'); 
	SET @ops_id = '9';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='reject' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-approveagent'); 
	SET @ops_id = '9';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='view' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

        -- Rejecetd Agents
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-approveagent'); 
	SET @ops_id = '9';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='rejectedlist' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');


    -- Product
	-- Index
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-agents'); 
	SET @ops_id = '5';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='index' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');
       
        --Agents
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-agentsummary'); 
	SET @ops_id = '5';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='index' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-agents'); 
	SET @ops_id = '5';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='edit' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-agentsummary'); 
	SET @ops_id = '5';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='block' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-agentsummary'); 
	SET @ops_id = '5';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='unblock' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-agentsummary'); 
	SET @ops_id = '5';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='unlock' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-agentsummary'); 
	SET @ops_id = '5';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='view' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

        -- Approval Pending
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-approveagent'); 
	SET @ops_id = '5';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='index' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-approveagent'); 
	SET @ops_id = '5';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='approve' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-approveagent'); 
	SET @ops_id = '5';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='reject' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-approveagent'); 
	SET @ops_id = '5';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='view' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');


    -- Accounts
	-- Index
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-agents'); 
	SET @ops_id = '6';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='index' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');
       
        --Agents
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-agentsummary'); 
	SET @ops_id = '6';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='index' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-agents'); 
	SET @ops_id = '6';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='edit' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-agentsummary'); 
	SET @ops_id = '6';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='block' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-agentsummary'); 
	SET @ops_id = '6';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='unblock' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-agentsummary'); 
	SET @ops_id = '6';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='unlock' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-agentsummary'); 
	SET @ops_id = '6';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='view' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

        -- Approval Pending
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-approveagent'); 
	SET @ops_id = '6';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='index' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-approveagent'); 
	SET @ops_id = '6';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='approve' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-approveagent'); 
	SET @ops_id = '6';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='reject' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-approveagent'); 
	SET @ops_id = '6';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='view' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

        -- Rejecetd Agents
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-approveagent'); 
	SET @ops_id = '6';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='rejectedlist' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');


    -- Sales
	-- Index
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-agents'); 
	SET @ops_id = '7';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='index' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

       
        --Agents
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-agentsummary'); 
	SET @ops_id = '7';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='index' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-agents'); 
	SET @ops_id = '7';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='edit' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-agentsummary'); 
	SET @ops_id = '7';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='block' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-agentsummary'); 
	SET @ops_id = '7';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='unblock' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-agentsummary'); 
	SET @ops_id = '7';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='unlock' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-agentsummary'); 
	SET @ops_id = '7';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='view' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

        -- Approval Pending
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-approveagent'); 
	SET @ops_id = '7';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='index' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-approveagent'); 
	SET @ops_id = '7';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='approve' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-approveagent'); 
	SET @ops_id = '7';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='reject' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-approveagent'); 
	SET @ops_id = '7';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='view' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

        -- Rejecetd Agents
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-approveagent'); 
	SET @ops_id = '7';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='rejectedlist' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');



-- Agent Funding
    -- Operation Helpdesk
	-- Index
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-agentfunding'); 
	SET @ops_id = '4';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='index' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

        -- Upload kotak Bank Statement
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-agentfunding'); 
	SET @ops_id = '4';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='uploadkotakbanktatement' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

    -- Operation (Maker)
	-- Index
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-agentfunding'); 
	SET @ops_id = '8';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='index' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

        -- Upload kotak Bank Statement
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-agentfunding'); 
	SET @ops_id = '8';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='uploadkotakbanktatement' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

        -- Pending Fund Request
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-agentfunding'); 
	SET @ops_id = '8';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='pendingfundrequest' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-agentfunding'); 
	SET @ops_id = '8';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='exportpendingfundrequest' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-agentsummary'); 
	SET @ops_id = '8';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='view' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-agentfunding'); 
	SET @ops_id = '8';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='confirmbeforesettlement' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-agentfunding'); 
	SET @ops_id = '8';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='confirmbeforerejectfundrequest' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

        -- Unsettled Bank Statement
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-agentfunding'); 
	SET @ops_id = '8';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='unsettledbankstatement' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-agentfunding'); 
	SET @ops_id = '8';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='exportunsettledbankstatement' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

        -- Settled Fund Request
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-agentfunding'); 
	SET @ops_id = '8';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='settledfundrequest' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-agentfunding'); 
	SET @ops_id = '8';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='exportsettledfundrequest' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-agentsummary'); 
	SET @ops_id = '8';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='view' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');


    -- Operation (Checker)
	-- Index
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-agentfunding'); 
	SET @ops_id = '9';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='index' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

        -- Upload kotak Bank Statement
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-agentfunding'); 
	SET @ops_id = '9';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='uploadkotakbanktatement' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

        -- Pending Fund Request
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-agentfunding'); 
	SET @ops_id = '9';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='pendingfundrequest' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-agentfunding'); 
	SET @ops_id = '9';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='exportpendingfundrequest' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-agentsummary'); 
	SET @ops_id = '9';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='view' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-agentfunding'); 
	SET @ops_id = '9';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='confirmbeforesettlement' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-agentfunding'); 
	SET @ops_id = '9';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='confirmbeforerejectfundrequest' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

        -- Unsettled Bank Statement
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-agentfunding'); 
	SET @ops_id = '9';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='unsettledbankstatement' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-agentfunding'); 
	SET @ops_id = '9';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='exportunsettledbankstatement' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

        -- Settled Fund Request
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-agentfunding'); 
	SET @ops_id = '9';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='settledfundrequest' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-agentfunding'); 
	SET @ops_id = '9';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='exportsettledfundrequest' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-agentsummary'); 
	SET @ops_id = '9';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='view' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');


    -- Product


    -- Accounts
	-- Index
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-agentfunding'); 
	SET @ops_id = '6';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='index' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

        -- Upload kotak Bank Statement
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-agentfunding'); 
	SET @ops_id = '6';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='uploadkotakbanktatement' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

        -- Pending Fund Request
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-agentfunding'); 
	SET @ops_id = '6';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='pendingfundrequest' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-agentfunding'); 
	SET @ops_id = '6';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='exportpendingfundrequest' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-agentsummary'); 
	SET @ops_id = '6';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='view' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-agentfunding'); 
	SET @ops_id = '6';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='confirmbeforesettlement' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-agentfunding'); 
	SET @ops_id = '6';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='confirmbeforerejectfundrequest' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

        -- Unsettled Bank Statement
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-agentfunding'); 
	SET @ops_id = '6';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='unsettledbankstatement' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-agentfunding'); 
	SET @ops_id = '6';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='exportunsettledbankstatement' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

        -- Settled Fund Request
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-agentfunding'); 
	SET @ops_id = '6';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='settledfundrequest' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-agentfunding'); 
	SET @ops_id = '6';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='exportsettledfundrequest' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-agentsummary'); 
	SET @ops_id = '6';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='view' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');


    -- Sales
	-- Index
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-agentfunding'); 
	SET @ops_id = '7';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='index' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

        -- Pending Fund Request
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-agentfunding'); 
	SET @ops_id = '7';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='pendingfundrequest' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-agentfunding'); 
	SET @ops_id = '7';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='exportpendingfundrequest' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-agentsummary'); 
	SET @ops_id = '7';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='view' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-agentfunding'); 
	SET @ops_id = '7';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='confirmbeforesettlement' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-agentfunding'); 
	SET @ops_id = '7';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='confirmbeforerejectfundrequest' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

        -- Settled Fund Request
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-agentfunding'); 
	SET @ops_id = '7';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='settledfundrequest' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-agentfunding'); 
	SET @ops_id = '7';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='exportsettledfundrequest' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-agentsummary'); 
	SET @ops_id = '7';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='view' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');



-- Reports
        -- Remitter Registration Report (Already Exists )
        -- Remitter Transactions Report (Except for sales)
        -- Remittance Commission Report (Already Exists )
        -- Remittance Transactions Report (Already Exists )
        -- Agent Wise Remittance Report (Already Exists ) 
        -- Remittance Exception Report (Already Exists )
        -- Agent-wise Remittance Commission Report (Already Exists )
        -- Remittance Refund Yet to claim Report (Already Exists )
        -- Remittance Response Report (Already Exists )
        -- Agent Commission Summary Report (Already Exists )
        -- Agent Balance Sheet (Already Exists )
        -- Fee Report (Already Exists )
        -- Agent wise Fee Report (Already Exists )
        -- Product wise Refund Report (Already Exists )
        -- Agent Authorized funding Report (Already Exists )
        -- Agent Wise Authorized Funding Report (Except for sales)
        -- Agent Unauthorized Funding Report (Already Exists )
        -- Beneficiary Exception More than 1 Lac (Only for Admin)
        -- Beneficiary Exception More than 10 Remitter (Do not exist)
        -- Beneficiary Registration Report (Only for Admin)

    -- Operation Helpdesk
        -- Beneficiary Exception More than 1 Lac 
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-remit_reports'); 
	SET @ops_id = '4';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='beneficiaryexception' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-remit_reports'); 
	SET @ops_id = '4';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='exportbeneficiaryexception' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

        -- Beneficiary Registration Report
--
SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-reports'); 

SET @ops_id = '3';

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'beneregistration', @flag_id, 'Beneficiary Registration Report');
SET @priv_id = last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

INSERT INTO `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'exportbeneregistration', @flag_id, 'Export Beneficiary Registration Report');
SET @priv_id = last_insert_id();
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1'); 


	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-reports'); 
	SET @ops_id = '4';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='beneregistration' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-remit_reports'); 
	SET @ops_id = '4';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='exportbeneregistration' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');


    -- Operation (Maker)
        -- Beneficiary Exception More than 1 Lac 
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-remit_reports'); 
	SET @ops_id = '8';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='beneficiaryexception' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-remit_reports'); 
	SET @ops_id = '8';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='exportbeneficiaryexception' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

        -- Beneficiary Registration Report
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-reports'); 
	SET @ops_id = '8';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='beneregistration' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-remit_reports'); 
	SET @ops_id = '8';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='exportbeneregistration' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');


    -- Operation (Checker)
        -- Beneficiary Exception More than 1 Lac 
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-remit_reports'); 
	SET @ops_id = '9';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='beneficiaryexception' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-remit_reports'); 
	SET @ops_id = '9';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='exportbeneficiaryexception' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

        -- Beneficiary Registration Report
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-reports'); 
	SET @ops_id = '9';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='beneregistration' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-remit_reports'); 
	SET @ops_id = '9';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='exportbeneregistration' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');


    -- Product
        -- Beneficiary Exception More than 1 Lac 
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-remit_reports'); 
	SET @ops_id = '5';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='beneficiaryexception' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-remit_reports'); 
	SET @ops_id = '5';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='exportbeneficiaryexception' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

        -- Beneficiary Registration Report
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-reports'); 
	SET @ops_id = '5';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='beneregistration' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-remit_reports'); 
	SET @ops_id = '5';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='exportbeneregistration' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');


    -- Accounts
        -- Beneficiary Exception More than 1 Lac 
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-remit_reports'); 
	SET @ops_id = '6';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='beneficiaryexception' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-remit_reports'); 
	SET @ops_id = '6';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='exportbeneficiaryexception' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

        -- Beneficiary Registration Report
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-reports'); 
	SET @ops_id = '6';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='beneregistration' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-remit_reports'); 
	SET @ops_id = '6';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='exportbeneregistration' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');


    -- Sales
	-- Remitter Transactions Report
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-remit_reports'); 
	SET @ops_id = '7';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='remittertransaction' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-remit_reports'); 
	SET @ops_id = '7';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='exportremittertransaction' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

	-- Agent Wise Authorized Funding Report
	SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-reports'); 
	SET @ops_id = '7';
	SET @priv_id := (SELECT id FROM `t_privileges` where name ='agentwisefundrequests' AND flag_id = @flag_id ); 
	INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');
