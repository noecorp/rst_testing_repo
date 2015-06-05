ALTER TABLE `rat_corp_cardholders`
MODIFY COLUMN `status`  enum('active','inactive','ecs_pending','ecs_failed','blocked') NOT NULL DEFAULT 'ecs_pending' AFTER `date_failed`,
ADD COLUMN `date_blocked`  datetime NULL AFTER `date_activation`;