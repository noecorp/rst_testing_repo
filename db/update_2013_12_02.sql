ALTER TABLE `t_products`
ADD COLUMN `flag_common`  enum('yes','no') NOT NULL DEFAULT 'no' AFTER `unicode`;

UPDATE t_products SET flag_common = 'yes' WHERE unicode in ('310', '510');