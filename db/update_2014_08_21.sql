ALTER TABLE `rat_remit_remitters`
DROP COLUMN `rat_customer_id`;

CREATE TABLE rat_update_corp_cardholders_log LIKE rat_corp_cardholders;

DROP TABLE IF EXISTS `rat_wallet_transfer`;
CREATE TABLE `rat_wallet_transfer` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `txn_code` int(11) unsigned NOT NULL,
  `rat_customer_id` int(11) unsigned NOT NULL,
  `txn_rat_customer_id` int(11) unsigned NOT NULL,
  `customer_purse_id` int(11) unsigned NOT NULL,
  `txn_customer_purse_id` int(11) unsigned NOT NULL,
  `amount` decimal(11,2) NOT NULL,
  `txn_type` char(4) NOT NULL,
  `status` enum('pending','success','failure') NOT NULL DEFAULT 'pending',
  `by_agent_id` int(11) unsigned NOT NULL,
  `date_created` datetime NOT NULL,
  `date_updated` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


INSERT INTO t_transaction_type (`typecode`, `name`, `status`, `date_created`, `is_comm`) VALUES ('WWFT', 'Wallet to Wallet Fund Transfer', 'active', CURRENT_TIMESTAMP, 'no');
