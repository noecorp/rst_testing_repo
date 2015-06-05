SET @product_id_val := (SELECT id FROM t_products WHERE unicode = '710');
UPDATE `boi_disbursement_batch` SET `product_id`=@product_id_val WHERE `product_id`=3;
