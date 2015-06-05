 ALTER TABLE `t_agent_details` CHANGE `gender` `gender` ENUM( 'male', 'female', 'instution' ) NOT NULL ,
CHANGE `Identification_type` `Identification_type` VARCHAR( 50 ) NOT NULL ,
CHANGE `address_proof_type` `address_proof_type` VARCHAR( 50 ) NOT NULL;

ALTER TABLE `t_agents` ADD `institution_name` VARCHAR( 50 ) NOT NULL AFTER `mobile2`;