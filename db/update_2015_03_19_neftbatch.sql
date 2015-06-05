ALTER TABLE `t_products` ADD `is_neftbatch` ENUM( 'yes', 'no' ) NOT NULL DEFAULT 'no' AFTER `status`;

UPDATE `t_products` SET `is_neftbatch`='yes' WHERE `unicode`='924' AND `const`='RAT_SMP' LIMIT 1;
