ALTER TABLE `rat_customer_product`
ADD COLUMN `product_customer_id`  int(11) UNSIGNED NULL AFTER `id`;

ALTER TABLE `t_agents`
ADD COLUMN `parent_agent_id`  int(16) NULL DEFAULT 0 AFTER `enroll_status`;

