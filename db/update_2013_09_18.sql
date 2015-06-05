ALTER TABLE `t_txn_ops`
ADD COLUMN `agent_funding_id`  int(11) UNSIGNED NULL AFTER `agent_fund_request_id`;

ALTER TABLE `t_txn_agent`
ADD COLUMN `agent_funding_id`  int(11) UNSIGNED NULL AFTER `agent_fund_request_id`;