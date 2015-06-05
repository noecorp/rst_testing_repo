#password = transerv
d58c03452e1527b7b1f2ce08e8ad3a8dae9e4774

# Create Operation User (Super user)
insert into t_operation_users ( firstname, lastname, username, password, email, mobile1 ) value 
(firstname, lastname, username, password, email, mobile);


insert into t_operation_users_groups ( group_id, user_id ) value 
(1, LAST_INSERT_ID());

#password = transerv
87c21e331dc41229762d5a711c892b65e22e200c    
insert into t_agents ( username, email, password, active, mobile1, registration_type ) value 
(username, email, password, 'active', mobile, 'operations');
