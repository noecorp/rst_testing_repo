CREATE TABLE t_closed_loop_agents(
	f_id int AUTO_INCREMENT unique,
	f_agent_id int NOT NULL , 
	f_status varchar(20) check ( f_status in ( 'active', 'inactive')) ,
	f_date_created datetime NOT NULL ,
	f_date_updated TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
	f_by_agent_id int NOT NULL,
	f_group varchar(30) not null comment 'Group Name',
	primary key(f_agent_id, f_group)
	)ENGINE=InnoDB;
	

CREATE TABLE t_closed_loop_agents_log (
f_id int AUTO_INCREMENT,
f_master_id int,
f_agent_id int NOT NULL , 
f_status varchar(20)  check (f_status in ( 'active', 'inactive')) ,
f_date_created datetime NOT NULL ,
f_date_updated TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
f_by_agent_id int NOT NULL,
f_group varchar(30) not null comment 'Group Name',
primary key(f_id) ,
FOREIGN KEY (f_master_id) REFERENCES t_closed_loop_agents(f_Id) 
 )ENGINE=InnoDB;


insert into t_closed_loop_agents(f_agent_id,f_status,f_date_created,f_by_agent_id,f_group) values (449,'active',now(),449,'Forbes_group');
insert into t_closed_loop_agents_log(f_master_id,f_agent_id,f_status,f_date_created,f_by_agent_id,f_group) values (1,449,'active',now(),449,'Forbes_group');