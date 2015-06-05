ALTER TABLE `boi_corp_cardholders`
MODIFY COLUMN `status_ecs`  enum('pending','failure','success','waiting','in_process') NOT NULL DEFAULT 'waiting' AFTER `status_ops`;

ALTER TABLE `boi_corp_cardholders`
MODIFY COLUMN `status_ecs`  enum('pending','failure','success','waiting') NOT NULL DEFAULT 'waiting' AFTER `status_ops`;

ALTER TABLE `boi_corp_cardholders` CHANGE `nominee_city_cd` `nominee_city_cd` VARCHAR( 30 ) NOT NULL ;
