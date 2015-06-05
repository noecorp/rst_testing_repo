
INSERT INTO t_transaction_type (typecode,name,status,date_created,is_comm)
VALUES ('COMM','Commission','active',now(),'no');

INSERT INTO t_transaction_type (typecode,name,status,date_created,is_comm)
VALUES ('RCOM','Commission Reversal','active',now(),'no');

COMMIT;

