ALTER TABLE `rat_beneficiaries` ADD COLUMN `bank_id` int(11) UNSIGNED NOT NULL AFTER `id`;
UPDATE rat_beneficiaries SET bank_id = 3;