SET @purseId := (SELECT id  FROM `purse_master` WHERE `code` LIKE 'SUR915');

INSERT INTO `bind_purse_mcc` VALUES  (NULL, @purseId , '6011', 'active');
INSERT INTO `bind_purse_mcc` VALUES  (NULL, @purseId , '6010', 'active');