UPDATE t_products SET const = 'BOI_REMIT' WHERE id = 2 LIMIT 1;
ALTER TABLE `rat_beneficiaries` CHANGE `txn_code` `bene_code` BIGINT(16) UNSIGNED NULL DEFAULT NULL;

CREATE TABLE IF NOT EXISTS `t_benecode` (
  `txn_code` BIGINT(16) unsigned NOT NULL,
  `status` enum('free','used','block') DEFAULT 'free',
  `date_added` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
