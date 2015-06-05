ALTER TABLE `boi_card_mapping` CHANGE `status` `status` ENUM( 'pending', 'success', 'failure', 'mapped' ) NOT NULL DEFAULT 'pending';
ALTER TABLE `boi_card_mapping`
MODIFY COLUMN `date_created`  timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `batch_name`;
