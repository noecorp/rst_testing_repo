UPDATE boi_corp_load_request SET amount_available = 0, status = 'debited' WHERE (status = 'loaded' OR status = 'cutoff') AND mode = 'dr';
UPDATE rat_corp_load_request SET amount_available = 0, status = 'debited' WHERE (status = 'loaded' OR status = 'cutoff')  AND mode = 'dr';
UPDATE kotak_corp_load_request SET amount_available = 0, status = 'debited' WHERE (status = 'loaded' OR status = 'cutoff')  AND mode = 'dr';


