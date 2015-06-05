update t_closed_loop_agents set f_group = 'FORBES' where f_group = 'Forbes_group';

insert into t_closed_loop_agents_log(f_master_id,f_agent_id,f_status,f_date_created,f_by_ops_id,f_group) values (1,10377,'active',now(),0,'FORBES');

commit;