SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-aml'); 
SET @ops_id = '8';-- Maker

SET @priv_id := (SELECT id FROM `t_privileges` where name ='uploadaml' AND flag_id=@flag_id); 
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');


SET @priv_id := (SELECT id FROM `t_privileges` where name ='amlbyops' AND flag_id=@flag_id); 
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');


SET @priv_id := (SELECT id FROM `t_privileges` where name ='displayaml' AND flag_id=@flag_id); 
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

SET @priv_id := (SELECT id FROM `t_privileges` where name ='index' AND flag_id=@flag_id); 
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');


SET @priv_id := (SELECT id FROM `t_privileges` where name ='amlmatched' AND flag_id=@flag_id); 
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

SET @priv_id := (SELECT id FROM `t_privileges` where name ='kotakremitters' AND flag_id=@flag_id); 
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');


SET @priv_id := (SELECT id FROM `t_privileges` where name ='kotakbeneficiaries' AND flag_id=@flag_id); 
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

SET @priv_id := (SELECT id FROM `t_privileges` where name ='kotakremitterdetail' AND flag_id=@flag_id); 
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

SET @priv_id := (SELECT id FROM `t_privileges` where name ='kotakbeneficiarydetail' AND flag_id=@flag_id); 
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

SET @priv_id := (SELECT id FROM `t_privileges` where name ='amlrejectedagents' AND flag_id=@flag_id); 
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

SET @priv_id := (SELECT id FROM `t_privileges` where name ='exportamlrejectedagents' AND flag_id=@flag_id); 
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

SET @priv_id := (SELECT id FROM `t_privileges` where name ='exportkotakbeneficiaries' AND flag_id=@flag_id); 
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

SET @priv_id := (SELECT id FROM `t_privileges` where name ='exportkotakremitters' AND flag_id=@flag_id); 
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

SET @priv_id := (SELECT id FROM `t_privileges` where name ='kotakindex' AND flag_id=@flag_id); 
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

SET @priv_id := (SELECT id FROM `t_privileges` where name ='kotakcardholders' AND flag_id=@flag_id); 
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

SET @priv_id := (SELECT id FROM `t_privileges` where name ='kotakcardholderdetail' AND flag_id=@flag_id); 
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

SET @priv_id := (SELECT id FROM `t_privileges` where name ='kotakduplicateremitters' AND flag_id=@flag_id); 
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

SET @priv_id := (SELECT id FROM `t_privileges` where name ='kotakremitterduplicatedetails' AND flag_id=@flag_id); 
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

SET @priv_id := (SELECT id FROM `t_privileges` where name ='kotakduplicatebeneficiary' AND flag_id=@flag_id); 
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

SET @priv_id := (SELECT id FROM `t_privileges` where name ='kotakbeneficiaryduplicatedetails' AND flag_id=@flag_id); 
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

SET @priv_id := (SELECT id FROM `t_privileges` where name ='kotakduplicatecardholder' AND flag_id=@flag_id); 
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

SET @priv_id := (SELECT id FROM `t_privileges` where name ='kotakcardholderduplicatedetails' AND flag_id=@flag_id); 
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

SET @priv_id := (SELECT id FROM `t_privileges` where name ='ratnakarindex' AND flag_id=@flag_id); 
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

SET @priv_id := (SELECT id FROM `t_privileges` where name ='ratnakarbeneficiaries' AND flag_id=@flag_id); 
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

SET @priv_id := (SELECT id FROM `t_privileges` where name ='ratnakarremitters' AND flag_id=@flag_id); 
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

SET @priv_id := (SELECT id FROM `t_privileges` where name ='ratnakarcardholders' AND flag_id=@flag_id); 
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

SET @priv_id := (SELECT id FROM `t_privileges` where name ='exportratnakarremitters' AND flag_id=@flag_id); 
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

SET @priv_id := (SELECT id FROM `t_privileges` where name ='exportratnakarcardholders' AND flag_id=@flag_id); 
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

SET @priv_id := (SELECT id FROM `t_privileges` where name ='exportratnakarbeneficiaries' AND flag_id=@flag_id); 
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

SET @priv_id := (SELECT id FROM `t_privileges` where name ='ratnakarremitterdetail' AND flag_id=@flag_id); 
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

SET @priv_id := (SELECT id FROM `t_privileges` where name ='ratnakarbeneficiarydetail' AND flag_id=@flag_id); 
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

SET @priv_id := (SELECT id FROM `t_privileges` where name ='ratnakarcardholderdetail' AND flag_id=@flag_id); 
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

SET @priv_id := (SELECT id FROM `t_privileges` where name ='boiindex' AND flag_id=@flag_id); 
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

SET @priv_id := (SELECT id FROM `t_privileges` where name ='boibeneficiaries' AND flag_id=@flag_id); 
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');


SET @priv_id := (SELECT id FROM `t_privileges` where name ='boiremitters' AND flag_id=@flag_id); 
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

SET @priv_id := (SELECT id FROM `t_privileges` where name ='boicardholders' AND flag_id=@flag_id); 
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

SET @priv_id := (SELECT id FROM `t_privileges` where name ='exportboiremitters' AND flag_id=@flag_id); 
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

SET @priv_id := (SELECT id FROM `t_privileges` where name ='exportboicardholders' AND flag_id=@flag_id); 
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

SET @priv_id := (SELECT id FROM `t_privileges` where name ='exportboibeneficiaries' AND flag_id=@flag_id); 
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

SET @priv_id := (SELECT id FROM `t_privileges` where name ='boiremitterdetail' AND flag_id=@flag_id); 
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

SET @priv_id := (SELECT id FROM `t_privileges` where name ='boibeneficiarydetail' AND flag_id=@flag_id); 
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

SET @priv_id := (SELECT id FROM `t_privileges` where name ='boicardholderdetail' AND flag_id=@flag_id); 
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');


SET @priv_id := (SELECT id FROM `t_privileges` where name ='exportkotakcardholders' AND flag_id=@flag_id); 
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

SET @priv_id := (SELECT id FROM `t_privileges` where name ='bankindex' AND flag_id=@flag_id); 
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');


-- Checker

SET @ops_id = '9';-- Checker

SET @priv_id := (SELECT id FROM `t_privileges` where name ='uploadaml' AND flag_id=@flag_id); 
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');


SET @priv_id := (SELECT id FROM `t_privileges` where name ='amlbyops' AND flag_id=@flag_id); 
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');


SET @priv_id := (SELECT id FROM `t_privileges` where name ='displayaml' AND flag_id=@flag_id); 
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

SET @priv_id := (SELECT id FROM `t_privileges` where name ='index' AND flag_id=@flag_id); 
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');


SET @priv_id := (SELECT id FROM `t_privileges` where name ='amlmatched' AND flag_id=@flag_id); 
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

SET @priv_id := (SELECT id FROM `t_privileges` where name ='kotakremitters' AND flag_id=@flag_id); 
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');


SET @priv_id := (SELECT id FROM `t_privileges` where name ='kotakbeneficiaries' AND flag_id=@flag_id); 
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

SET @priv_id := (SELECT id FROM `t_privileges` where name ='kotakremitterdetail' AND flag_id=@flag_id); 
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

SET @priv_id := (SELECT id FROM `t_privileges` where name ='kotakbeneficiarydetail' AND flag_id=@flag_id); 
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

SET @priv_id := (SELECT id FROM `t_privileges` where name ='amlrejectedagents' AND flag_id=@flag_id); 
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

SET @priv_id := (SELECT id FROM `t_privileges` where name ='exportamlrejectedagents' AND flag_id=@flag_id); 
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

SET @priv_id := (SELECT id FROM `t_privileges` where name ='exportkotakbeneficiaries' AND flag_id=@flag_id); 
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

SET @priv_id := (SELECT id FROM `t_privileges` where name ='exportkotakremitters' AND flag_id=@flag_id); 
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

SET @priv_id := (SELECT id FROM `t_privileges` where name ='kotakindex' AND flag_id=@flag_id); 
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

SET @priv_id := (SELECT id FROM `t_privileges` where name ='kotakcardholders' AND flag_id=@flag_id); 
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

SET @priv_id := (SELECT id FROM `t_privileges` where name ='kotakcardholderdetail' AND flag_id=@flag_id); 
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

SET @priv_id := (SELECT id FROM `t_privileges` where name ='kotakduplicateremitters' AND flag_id=@flag_id); 
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

SET @priv_id := (SELECT id FROM `t_privileges` where name ='kotakremitterduplicatedetails' AND flag_id=@flag_id); 
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

SET @priv_id := (SELECT id FROM `t_privileges` where name ='kotakduplicatebeneficiary' AND flag_id=@flag_id); 
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

SET @priv_id := (SELECT id FROM `t_privileges` where name ='kotakbeneficiaryduplicatedetails' AND flag_id=@flag_id); 
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

SET @priv_id := (SELECT id FROM `t_privileges` where name ='kotakduplicatecardholder' AND flag_id=@flag_id); 
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

SET @priv_id := (SELECT id FROM `t_privileges` where name ='kotakcardholderduplicatedetails' AND flag_id=@flag_id); 
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

SET @priv_id := (SELECT id FROM `t_privileges` where name ='ratnakarindex' AND flag_id=@flag_id); 
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

SET @priv_id := (SELECT id FROM `t_privileges` where name ='ratnakarbeneficiaries' AND flag_id=@flag_id); 
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

SET @priv_id := (SELECT id FROM `t_privileges` where name ='ratnakarremitters' AND flag_id=@flag_id); 
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

SET @priv_id := (SELECT id FROM `t_privileges` where name ='ratnakarcardholders' AND flag_id=@flag_id); 
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

SET @priv_id := (SELECT id FROM `t_privileges` where name ='exportratnakarremitters' AND flag_id=@flag_id); 
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

SET @priv_id := (SELECT id FROM `t_privileges` where name ='exportratnakarcardholders' AND flag_id=@flag_id); 
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

SET @priv_id := (SELECT id FROM `t_privileges` where name ='exportratnakarbeneficiaries' AND flag_id=@flag_id); 
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

SET @priv_id := (SELECT id FROM `t_privileges` where name ='ratnakarremitterdetail' AND flag_id=@flag_id); 
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

SET @priv_id := (SELECT id FROM `t_privileges` where name ='ratnakarbeneficiarydetail' AND flag_id=@flag_id); 
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

SET @priv_id := (SELECT id FROM `t_privileges` where name ='ratnakarcardholderdetail' AND flag_id=@flag_id); 
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

SET @priv_id := (SELECT id FROM `t_privileges` where name ='boiindex' AND flag_id=@flag_id); 
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

SET @priv_id := (SELECT id FROM `t_privileges` where name ='boibeneficiaries' AND flag_id=@flag_id); 
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');


SET @priv_id := (SELECT id FROM `t_privileges` where name ='boiremitters' AND flag_id=@flag_id); 
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

SET @priv_id := (SELECT id FROM `t_privileges` where name ='boicardholders' AND flag_id=@flag_id); 
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

SET @priv_id := (SELECT id FROM `t_privileges` where name ='exportboiremitters' AND flag_id=@flag_id); 
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

SET @priv_id := (SELECT id FROM `t_privileges` where name ='exportboicardholders' AND flag_id=@flag_id); 
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

SET @priv_id := (SELECT id FROM `t_privileges` where name ='exportboibeneficiaries' AND flag_id=@flag_id); 
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

SET @priv_id := (SELECT id FROM `t_privileges` where name ='boiremitterdetail' AND flag_id=@flag_id); 
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

SET @priv_id := (SELECT id FROM `t_privileges` where name ='boibeneficiarydetail' AND flag_id=@flag_id); 
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

SET @priv_id := (SELECT id FROM `t_privileges` where name ='boicardholderdetail' AND flag_id=@flag_id); 
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');


SET @priv_id := (SELECT id FROM `t_privileges` where name ='exportkotakcardholders' AND flag_id=@flag_id); 
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

SET @priv_id := (SELECT id FROM `t_privileges` where name ='bankindex' AND flag_id=@flag_id); 
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

--Help Desk
SET @ops_id = '4';

SET @priv_id := (SELECT id FROM `t_privileges` where name ='displayaml' AND flag_id=@flag_id); 
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

SET @priv_id := (SELECT id FROM `t_privileges` where name ='index' AND flag_id=@flag_id); 
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');


--Product
SET @ops_id = '5';

SET @priv_id := (SELECT id FROM `t_privileges` where name ='displayaml' AND flag_id=@flag_id); 
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

SET @priv_id := (SELECT id FROM `t_privileges` where name ='index' AND flag_id=@flag_id); 
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

-- 9398

SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-remit_kotak_remitter'); 
SET @ops_id = '8';-- Maker
SET @priv_id := (SELECT id FROM `t_privileges` where name ='beneficiary' AND flag_id=@flag_id); 
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

SET @ops_id = '9';-- Checker
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');



SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-corp_kotak_cardload'); 
SET @ops_id = '8';-- Maker
SET @priv_id := (SELECT id FROM `t_privileges` where name ='cardload' AND flag_id=@flag_id); 
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

SET @ops_id = '9';-- Checker
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-reports'); 
SET @ops_id = '8';-- Maker

SET @priv_id := (SELECT id FROM `t_privileges` where name ='customerregistration' AND flag_id=@flag_id); 
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

SET @priv_id := (SELECT id FROM `t_privileges` where name ='wallettxn' AND flag_id=@flag_id); 
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

SET @priv_id := (SELECT id FROM `t_privileges` where name ='wallettrialbalance' AND flag_id=@flag_id); 
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');



SET @ops_id = '9';-- Checker
SET @priv_id := (SELECT id FROM `t_privileges` where name ='customerregistration' AND flag_id=@flag_id); 
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

SET @priv_id := (SELECT id FROM `t_privileges` where name ='wallettxn' AND flag_id=@flag_id); 
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

SET @priv_id := (SELECT id FROM `t_privileges` where name ='wallettrialbalance' AND flag_id=@flag_id); 
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-corporatefunding'); 

SET @ops_id = '9';-- Checker
SET @priv_id := (SELECT id FROM `t_privileges` where name ='pendingfundrequest' AND flag_id=@flag_id); 
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

SET @priv_id := (SELECT id FROM `t_privileges` where name ='unsettledbankstatement' AND flag_id=@flag_id); 
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

SET @priv_id := (SELECT id FROM `t_privileges` where name ='settledfundrequest' AND flag_id=@flag_id); 
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');

SET @flag_id := (SELECT id FROM `t_flags` where name ='operation-fundtransfertype'); 
SET @ops_id = '9';-- Checker
SET @priv_id := (SELECT id FROM `t_privileges` where name ='index' AND flag_id=@flag_id); 
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');
SET @ops_id = '8';-- Maker
INSERT INTO `t_flippers` (`id`, `group_id`, `flag_id`, `privilege_id`, `allow`) VALUES (NULL, @ops_id, @flag_id, @priv_id, '1');
