ALTER TABLE `t_agent_details`
MODIFY COLUMN `branch_id`  varchar(15) NOT NULL AFTER `bank_ifsc_code`;