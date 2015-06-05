DELETE from `t_fee_structure` where `f_product_id` = 16 AND `f_txn_type_code` = 'RMFE' AND `f_min_cum_amount` = '1.00' AND `f_max_cum_amount` = '999.99' LIMIT 1;

UPDATE `t_fee_structure` SET `f_min_cum_amount` = '1.00' WHERE `f_product_id` = 16 AND `f_txn_type_code` = 'RMFE' AND `f_min_cum_amount` =  '1000.00' AND `f_max_cum_amount` = '5000.99';

ALTER TABLE `t_fee_structure` ADD `f_min` DECIMAL( 7, 2 ) NOT NULL AFTER `f_fee_rate`, ADD `f_max` DECIMAL( 7, 2 ) NOT NULL AFTER `f_min`;

UPDATE `t_fee_structure` SET `f_min` = '15.00', `f_max` = '75.00' WHERE `f_product_id` =16 AND `f_txn_type_code` = 'RMFE' AND `f_min_cum_amount` = '1.00' AND `f_max_cum_amount` = '5000.99';
