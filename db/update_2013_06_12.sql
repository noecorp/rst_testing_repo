UPDATE t_transaction_type SET is_comm = 'no' WHERE typecode = 'RRFE' LIMIT 1;


UPDATE t_fee_items SET txn_flat = 0, txn_pcnt = 0, txn_min = 0, txn_max = 0 WHERE typecode = 'RRFE';