/*
 *  26-03-2015
 */ 

INSERT INTO `t_flags` (`id`, `name`, `description`, `active_on_dev`, `active_on_prod`) VALUES (null, 'agent-agentcorpcardload', 'Agent Corporate Card Load', 0, 0);
SET @flag_id := last_insert_id();
INSERT INTO `t_privileges` (`id` ,`name` ,`flag_id` ,`description`) VALUES (NULL , 'load', @flag_id, 'Load to Corp cardholders by Agent Portal.');
SET @privilege_id := last_insert_id();
SET @product_id := (SELECT id FROM t_products WHERE unicode = '924' AND status = 'active' LIMIT 1);
Insert into `t_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @product_id, @flag_id, @privilege_id, '1');


SET @flag_id := (SELECT id FROM `t_flags` WHERE name='agent-agentcorpcardload');
INSERT INTO `t_privileges` (`id` ,`name` ,`flag_id` ,`description`) VALUES (NULL , 'doload', @flag_id, 'Complete Corp Card Load');

SET @privilege_id = last_insert_id();
SET @product_id := (SELECT id FROM t_products WHERE unicode = '924' AND status = 'active' LIMIT 1);
Insert into `t_product_privileges` (`id`, `product_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @product_id, @flag_id, @privilege_id, '1');

INSERT INTO `t_unicode_conf` (`unicode_initials`, `bank_unicode`, `product_unicode`) VALUES (30092400, 300, 924); 



/*
 *  27-03-2015
 */


ALTER TABLE `rat_remittance_status_log` MODIFY COLUMN `status_old`  enum('in_process','success','failure','incomplete','hold','refund','processed') NULL DEFAULT 'in_process' AFTER `remittance_request_id`, MODIFY COLUMN `status_new`  enum('in_process','processed','success','failure','refund','incomplete','hold') NULL DEFAULT 'in_process' AFTER `status_old`;


/*
 *  27-03-2015
 */
/*
    Add column channel to get by which 
    channel(api,ops,agent or corporate) 
    this cardholder is registed. or load is done
*/

ALTER TABLE `rat_corp_cardholders` ADD COLUMN `channel` VARCHAR(10) NOT NULL AFTER `by_agent_id`;


ALTER TABLE `rat_corp_load_request` ADD COLUMN `channel` VARCHAR(10) NOT NULL AFTER `by_corporate_id`;
