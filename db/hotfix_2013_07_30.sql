SET @agent_id := (SELECT id FROM `t_agents` WHERE agent_code = '42128220016' LIMIT 1);
UPDATE t_agent_closing_balance SET closing_balance = '895.00' WHERE agent_id = @agent_id AND date = '2013-07-29' LIMIT 1;