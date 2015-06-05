ALTER TABLE `t_bank`
ADD COLUMN `logo`  varchar(50) NULL AFTER `unicode`;

UPDATE t_bank SET logo = 'logo-boi.png' WHERE name = 'BANK OF INDIA';
UPDATE t_bank SET logo = 'logo-axis.jpg' WHERE name like 'AXIS BANK%';