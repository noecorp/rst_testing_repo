CREATE TABLE t_fee_structure(
	f_id INT AUTO_INCREMENT UNIQUE,
	f_product_id INT NOT NULL REFERENCES t_products(id),
	f_txn_type_code CHAR(4) NOT NULL REFERENCES t_transaction_type(typecode),
	f_txn_type_desc VARCHAR(30) NOT NULL,
	f_min_cum_amount DECIMAL(11,2) NOT NULL,
	f_max_cum_amount DECIMAL(11,2) NOT NULL,
	f_is_pct BOOLEAN NOT NULL,
	f_fee_rate DECIMAL(7,2) NOT NULL DEFAULT 0.00,
	f_status VARCHAR(20) CHECK ( f_status IN ( 'active', 'inactive')),
	f_by_ops_id INT NOT NULL,
	f_date_created DATETIME NOT NULL ,
	f_date_updated TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
	PRIMARY KEY(f_product_id, f_txn_type_code, f_min_cum_amount, f_max_cum_amount)
	)ENGINE=InnoDB;

CREATE TABLE t_fee_structure_log (
	f_id INT AUTO_INCREMENT,
	f_master_id INT,
	f_product_id INT NOT NULL REFERENCES t_products(id),
	f_txn_type_code CHAR(4) NOT NULL REFERENCES t_transaction_type(typecode),
	f_txn_type_desc VARCHAR(30) NOT NULL,
	f_min_cum_amount DECIMAL(11,2) NOT NULL,
	f_max_cum_amount DECIMAL(11,2) NOT NULL,
	f_is_pct BOOLEAN NOT NULL,
	f_fee_rate DECIMAL(7,2) NOT NULL DEFAULT 0.00,
	f_status VARCHAR(20) CHECK (f_status IN ( 'active', 'inactive')),
	f_by_ops_id INT NOT NULL,
	f_date_created DATETIME NOT NULL ,
	f_date_updated TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
PRIMARY KEY(f_id) ,
FOREIGN KEY (f_master_id) REFERENCES t_fee_structure(f_id) 
 )ENGINE=InnoDB;

/*For RBL*/
INSERT INTO t_fee_structure(f_product_id,f_txn_type_code,f_txn_type_desc,f_min_cum_amount,f_max_cum_amount,f_is_pct,f_fee_rate, f_status, f_by_ops_id, f_date_created, f_date_updated) 
VALUES(16, 'RMFE','Remittance Fee', 1000.00,5000.99,TRUE,1.5,'active',0,NOW(),NOW());

INSERT INTO t_fee_structure(f_product_id,f_txn_type_code,f_txn_type_desc,f_min_cum_amount,f_max_cum_amount,f_is_pct,f_fee_rate, f_status, f_by_ops_id, f_date_created, f_date_updated) 
VALUES(16, 'RMFE','Remittance Fee', 5001.00,10000.99,FALSE,100.00,'active',0,NOW(),NOW());

INSERT INTO t_fee_structure(f_product_id,f_txn_type_code,f_txn_type_desc,f_min_cum_amount,f_max_cum_amount,f_is_pct,f_fee_rate, f_status, f_by_ops_id, f_date_created, f_date_updated) 
VALUES(16, 'RMFE','Remittance Fee', 10001.00,15000.99,FALSE,150.00,'active',0,NOW(),NOW());

INSERT INTO t_fee_structure(f_product_id,f_txn_type_code,f_txn_type_desc,f_min_cum_amount,f_max_cum_amount,f_is_pct,f_fee_rate, f_status, f_by_ops_id, f_date_created, f_date_updated) 
VALUES(16, 'RMFE','Remittance Fee', 15001.00,20000.99,FALSE,200.00,'active',0,NOW(),NOW());

INSERT INTO t_fee_structure(f_product_id,f_txn_type_code,f_txn_type_desc,f_min_cum_amount,f_max_cum_amount,f_is_pct,f_fee_rate, f_status, f_by_ops_id, f_date_created, f_date_updated) 
VALUES(16, 'RMFE','Remittance Fee', 20001.00,25000.99,FALSE,250.00,'active',0,NOW(),NOW());


INSERT INTO t_fee_structure_log(f_product_id,f_txn_type_code,f_txn_type_desc,f_min_cum_amount,f_max_cum_amount,f_is_pct,f_fee_rate, f_status, f_by_ops_id, f_date_created, f_date_updated) 
VALUES(16, 'RMFE','Remittance Fee', 1000.00,5000.99,TRUE,1.5,'active',0,NOW(),NOW());

INSERT INTO t_fee_structure_log(f_product_id,f_txn_type_code,f_txn_type_desc,f_min_cum_amount,f_max_cum_amount,f_is_pct,f_fee_rate, f_status, f_by_ops_id, f_date_created, f_date_updated) 
VALUES(16, 'RMFE','Remittance Fee', 5001.00,10000.99,FALSE,100.00,'active',0,NOW(),NOW());

INSERT INTO t_fee_structure_log(f_product_id,f_txn_type_code,f_txn_type_desc,f_min_cum_amount,f_max_cum_amount,f_is_pct,f_fee_rate, f_status, f_by_ops_id, f_date_created, f_date_updated) 
VALUES(16, 'RMFE','Remittance Fee', 10001.00,15000.99,FALSE,150.00,'active',0,NOW(),NOW());

INSERT INTO t_fee_structure_log(f_product_id,f_txn_type_code,f_txn_type_desc,f_min_cum_amount,f_max_cum_amount,f_is_pct,f_fee_rate, f_status, f_by_ops_id, f_date_created, f_date_updated) 
VALUES(16, 'RMFE','Remittance Fee', 15001.00,20000.99,FALSE,200.00,'active',0,NOW(),NOW());

INSERT INTO t_fee_structure_log(f_product_id,f_txn_type_code,f_txn_type_desc,f_min_cum_amount,f_max_cum_amount,f_is_pct,f_fee_rate, f_status, f_by_ops_id, f_date_created, f_date_updated) 
VALUES(16, 'RMFE','Remittance Fee', 20001.00,25000.99,FALSE,250.00,'active',0,NOW(),NOW());


COMMIT;