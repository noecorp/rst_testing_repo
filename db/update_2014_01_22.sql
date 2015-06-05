SET @flg_id := (SELECT id FROM `t_flags` where name ='agent-corp_boi_customer');


Insert into `t_privileges` (`id`, `name`, `flag_id`, `description`) VALUES (NULL, 'edit', @flg_id, 'Edit Customer Details and resubmit to Ops');
SET @privilege_id = last_insert_id();

Insert into `t_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, '7', @flg_id, @privilege_id, '1');