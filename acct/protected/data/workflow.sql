CREATE DATABASE workflow CHARACTER SET utf8 COLLATE utf8_general_ci;

GRANT SELECT, INSERT, UPDATE, DELETE, EXECUTE ON workflow.* TO 'swuser'@'localhost' IDENTIFIED BY 'swisher168';

use workflow;

DROP TABLE IF EXISTS wf_process;
CREATE TABLE wf_process(
	id int unsigned not null auto_increment primary key,
	code varchar(15) not null,
	name varchar(255) not null,
	lcd timestamp default CURRENT_TIMESTAMP,
	lud timestamp default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
INSERT INTO wf_process(id, code, name) values(1, 'PAYMENT', 'Payment Approval Process');

DROP TABLE IF EXISTS wf_process_version;
CREATE TABLE wf_process_version(
	id int unsigned not null auto_increment primary key,
	process_id int unsigned not null,
	start_dt datetime not null,
	end_dt datetime not null,
	lcd timestamp default CURRENT_TIMESTAMP,
	lud timestamp default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
INSERT INTO wf_process_version(id, process_id, start_dt, end_dt) values(1, 1, '2016-01-01', '2017-05-26');
INSERT INTO wf_process_version(id, process_id, start_dt, end_dt) values(2, 1, '2017-05-27', '2099-12-31');

DROP TABLE IF EXISTS wf_action;
CREATE TABLE wf_action(
	id int unsigned not null auto_increment primary key,
	proc_ver_id int unsigned not null,
	code varchar(15) not null,
	name varchar(255) not null,
	lcd timestamp default CURRENT_TIMESTAMP,
	lud timestamp default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
INSERT INTO wf_action(id, proc_ver_id, code, name) values
(1,1,'APPROVE','批准付款申请'),
(2,1,'DENY','拒绝付款申请'),
(3,1,'SUBMIT','提交付款申请'),
(4,1,'CANCEL','取消付款申请'),
(5,1,'REIMBURSE','报销单申请'),
(6,1,'REIMAPPR','报销单签字'),
(7,1,'REIMCANCEL','取消报销单申请')
;
INSERT INTO wf_action(id, proc_ver_id, code, name) values
(8,2,'APPROVE','批准付款申请'),
(9,2,'DENY','拒绝付款申请'),
(10,2,'SUBMIT','提交付款申请'),
(11,2,'CANCEL','取消付款申请'),
(12,2,'REIMBURSE','报销单申请'),
(13,2,'REIMAPPR','报销单签字'),
(14,2,'REIMCANCEL','取消报销单申请'),
(15,2,'REQUEST','要求覆核付款申请'),
(16,2,'CHECK','覆核并提交付款申请')
;

DROP TABLE IF EXISTS wf_task;
CREATE TABLE wf_task(
	id int unsigned not null auto_increment primary key,
	proc_ver_id int unsigned not null,
	name varchar(255) not null,
	function_call varchar(255) not null,
	param varchar(1000),
	lcd timestamp default CURRENT_TIMESTAMP,
	lud timestamp default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
INSERT INTO wf_task(id, proc_ver_id, name, function_call, param) values
(1,1,'Send Email','sendEmail',''),
(2,1,'Status=Pending for Approval','transit','PA'),
(3,1,'Status=Approved','transit','A'),
(4,1,'Status=Denied','transit','D'),
(5,1,'Status=Pending for Reimbursement','transit','PR'),
(6,1,'Generate Transaction','generateTransaction',''),
(7,1,'Status=Reimbursed','transit','RE'),
(8,1,'Status=Pending for Reimbursement Approval','transit','PS'),
(9,1,'Status=Signed','transit','SI'),
(10,1,'Route to Approver','routeToApprover',''),
(11,1,'Route to Signer','routeToSigner',''),
(12,1,'Route to Requestor','routeToRequestor',''),
(13,1,'Status=End','transit','ED'),
(14,1,'Status=Cancel','transit','C'),
(15,1,'Clear All Pending','clearAllPending',''),
(16,1,'Status=Cancel','transit','RC')
;
INSERT INTO wf_task(id, proc_ver_id, name, function_call, param) values
(17,2,'Send Email','sendEmail',''),
(18,2,'Status=Pending for Approval','transit','PA'),
(19,2,'Status=Approved','transit','A'),
(20,2,'Status=Denied','transit','D'),
(21,2,'Status=Pending for Reimbursement','transit','PR'),
(22,2,'Generate Transaction','generateTransaction',''),
(23,2,'Status=Reimbursed','transit','RE'),
(24,2,'Status=Pending for Reimbursement Approval','transit','PS'),
(25,2,'Status=Signed','transit','SI'),
(26,2,'Route to Approver','routeToApprover',''),
(27,2,'Route to Signer','routeToSigner',''),
(28,2,'Route to Requestor','routeToRequestor',''),
(29,2,'Status=End','transit','ED'),
(30,2,'Status=Cancel','transit','C'),
(31,2,'Clear All Pending','clearAllPending',''),
(32,2,'Status=Cancel','transit','RC'),
(33,2,'Status=Checked','transit','CK'),
(34,2,'Status=Pending for Checking','transit','PC'),
(35,2,'Route to Account','routeToAccount','')
;

DROP TABLE IF EXISTS wf_action_task;
CREATE TABLE wf_action_task(
	action_id int unsigned not null,
	task_id int unsigned not null,
	seq_no int unsigned not null,
	lcd timestamp default CURRENT_TIMESTAMP,
	lud timestamp default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
INSERT INTO wf_action_task(action_id, task_id, seq_no) values
(1,3,1),
(1,1,2),
(1,15,3),
(1,5,4),
(1,12,5),
(2,4,1),
(2,1,2),
(2,13,3),
(2,15,4),
(3,2,1),
(3,10,2),
(3,1,3),
(4,14,1),
(4,1,2),
(4,13,3),
(4,15,4),
(5,7,1),
(5,8,2),
(5,11,3),
(5,1,4),
(5,6,4),
(6,9,1),
(6,1,2),
(6,13,3),
(7,16,1),
(7,1,2),
(7,13,3)
;
INSERT INTO wf_action_task(action_id, task_id, seq_no) values
(1+7,3+16,1),
(1+7,1+16,2),
(1+7,15+16,3),
(1+7,5+16,4),
(1+7,12+16,5),
(2+7,4+16,1),
(2+7,1+16,2),
(2+7,13+16,3),
(2+7,15+16,4),
(3+7,2+16,3),
(3+7,10+16,4),
(3+7,1+16,5),
(4+7,14+16,1),
(4+7,1+16,2),
(4+7,13+16,3),
(4+7,15+16,4),
(5+7,7+16,1),
(5+7,8+16,2),
(5+7,11+16,3),
(5+7,1+16,4),
(5+7,6+16,4),
(6+7,9+16,1),
(6+7,1+16,2),
(6+7,13+16,3),
(7+7,16+16,1),
(7+7,1+16,2),
(7+7,13+16,3),
(15,34,1),
(15,35,2),
(16,33,1),
(16,31,2),
(16,2+16,3),
(16,10+16,4),
(16,1+16,5)
;

DROP TABLE IF EXISTS wf_state;
CREATE TABLE wf_state(
	id int unsigned not null auto_increment primary key,
	proc_ver_id int unsigned not null,
	code char(2) not null,
	name varchar(255) not null,
	lcd timestamp default CURRENT_TIMESTAMP,
	lud timestamp default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
INSERT INTO wf_state(id, proc_ver_id, code, name) VALUES
(1,1,'ST','开始'),
(2,1,'ED','结束'),
(3,1,'PA','有待申请审核'),
(4,1,'PR','有待报销单申请'),
(5,1,'PS','有待报销单审批'),
(6,1,'A','已批准付款申请'),
(7,1,'D','已拒绝付款申请'),
(8,1,'C','已取消付款申请'),
(9,1,'RE','已申请报销单'),
(10,1,'SI','已签字报销单'),
(11,1,'RC','已取消报销单申请')
;
INSERT INTO wf_state(id, proc_ver_id, code, name) VALUES
(12,2,'ST','开始'),
(13,2,'ED','结束'),
(14,2,'PA','有待申请审核'),
(15,2,'PR','有待报销单申请'),
(16,2,'PS','有待报销单审批'),
(17,2,'A','已批准付款申请'),
(18,2,'D','已拒绝付款申请'),
(19,2,'C','已取消付款申请'),
(20,2,'RE','已申请报销单'),
(21,2,'SI','已签字报销单'),
(22,2,'RC','已取消报销单申请'),
(23,2,'PC','有待覆核付款申请'),
(24,2,'CK','已覆核付款申请')
;

DROP TABLE IF EXISTS wf_transition;
CREATE TABLE wf_transition(
	id int unsigned not null auto_increment primary key,
	proc_ver_id int unsigned not null,
	current_state int unsigned not null,
	next_state int unsigned not null,
	lcd timestamp default CURRENT_TIMESTAMP,
	lud timestamp default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
INSERT INTO wf_transition(proc_ver_id, current_state, next_state) VALUES
(1,1,3),
(1,3,6),
(1,3,7),
(1,6,4),
(1,7,2),
(1,4,9),
(1,9,5),
(1,5,10),
(1,10,2),
(1,3,8),
(1,8,2),
(1,4,11),
(1,11,2)
;
INSERT INTO wf_transition(proc_ver_id, current_state, next_state) VALUES
(2,1+11,23),
(2,1+11,3+11),
(2,23,24),
(2,24,3+11),
(2,3+11,6+11),
(2,3+11,7+11),
(2,6+11,4+11),
(2,7+11,2+11),
(2,4+11,9+11),
(2,9+11,5+11),
(2,5+11,10+11),
(2,10+11,2+11),
(2,3+11,8+11),
(2,8+11,2+11),
(2,4+11,11+11),
(2,11+11,2+11)
;

DROP TABLE IF EXISTS wf_request;
CREATE TABLE wf_request(
	id int unsigned not null auto_increment primary key,
	proc_ver_id int unsigned not null,
	current_state int unsigned not null,
	doc_id int unsigned not null,
	lcd timestamp default CURRENT_TIMESTAMP,
	lud timestamp default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS wf_request_data;
CREATE TABLE wf_request_data(
	id int unsigned not null auto_increment primary key,
	request_id int unsigned not null,
	data_name varchar(100) not null,
	data_value varchar(5000),
	lcd timestamp default CURRENT_TIMESTAMP,
	lud timestamp default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
	UNIQUE KEY request (request_id, data_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS wf_request_transit_log;
CREATE TABLE wf_request_transit_log(
	id int unsigned not null auto_increment primary key,
	request_id int unsigned not null,
	old_state int unsigned not null,
	new_state int unsigned not null,
	lcd timestamp default CURRENT_TIMESTAMP,
	lud timestamp default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS wf_request_resp_user;
CREATE TABLE wf_request_resp_user(
	id int unsigned not null auto_increment primary key,
	request_id int unsigned not null,
	log_id int unsigned not null,
	current_state int unsigned not null,
	username varchar(30) not null,
	status char(1) not null default 'P',
	action_id int unsigned default 0,
	lcd timestamp default CURRENT_TIMESTAMP,
	lud timestamp default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DELIMITER //
DROP FUNCTION IF EXISTS RequestStatus //
CREATE FUNCTION RequestStatus(p_proc_code varchar(15), p_doc_id int unsigned, p_req_dt datetime) RETURNS char(2)
BEGIN
	DECLARE status char(2);
	SET status = (
		SELECT d.code
		FROM wf_request a, wf_process b, wf_process_version c, wf_state d 
		WHERE a.proc_ver_id = c.id
		and b.id = c.process_id
		and a.current_state = d.id
		and b.code = p_proc_code
		and c.start_dt <= p_req_dt
		and c.end_dt >= p_req_dt
		and a.doc_id = p_doc_id
		LIMIT 1
	);
	RETURN status;
END //
DELIMITER ;

DELIMITER //
DROP FUNCTION IF EXISTS RequestStatusEx //
CREATE FUNCTION RequestStatusEx(p_proc_code varchar(15), p_doc_id int unsigned, p_req_dt datetime) RETURNS char(2)
BEGIN
	DECLARE status char(2);
	
	SET status = (
		SELECT IF(d.code<>'ED',d.code,f.code) as status
		FROM wf_request a, wf_process b, wf_process_version c, wf_state d, wf_request_transit_log e, wf_state f 
		WHERE a.proc_ver_id = c.id
		and b.id = c.process_id
		and a.current_state = d.id
		and b.code = p_proc_code
		and c.start_dt <= p_req_dt
		and c.end_dt >= p_req_dt
		and a.doc_id = p_doc_id
		and a.id = e.request_id
		and a.current_state = e.new_state
		and e.old_state = f.id
		order by e.id desc 
		LIMIT 1
	);
	RETURN status;
END //
DELIMITER ;

DELIMITER //
DROP FUNCTION IF EXISTS RequestStatusDesc //
CREATE FUNCTION RequestStatusDesc(p_proc_code varchar(15), p_doc_id int unsigned, p_req_dt datetime) RETURNS char(255)
BEGIN
	DECLARE status_desc char(255);
	
	SET status_desc = (
		SELECT IF(d.code<>'ED',d.name,CONCAT(f.name,' (',d.name,')')) as status_desc
		FROM wf_request a, wf_process b, wf_process_version c, wf_state d, wf_request_transit_log e, wf_state f 
		WHERE a.proc_ver_id = c.id
		and b.id = c.process_id
		and a.current_state = d.id
		and b.code = p_proc_code
		and c.start_dt <= p_req_dt
		and c.end_dt >= p_req_dt
		and a.doc_id = p_doc_id
		and a.id = e.request_id
		and a.current_state = e.new_state
		and e.old_state = f.id
		order by e.id desc 
		LIMIT 1
	);
	RETURN status_desc;
END //
DELIMITER ;

DELIMITER //
DROP FUNCTION IF EXISTS RequestStatusDate //
CREATE FUNCTION RequestStatusDate(p_proc_code varchar(15), p_doc_id int unsigned, p_req_dt datetime, p_code char(2)) RETURNS datetime
BEGIN
	DECLARE status_dt datetime;
	SET status_dt = (
		SELECT e.lcd
		FROM wf_request a, wf_process b, wf_process_version c, wf_state d, wf_request_transit_log e
		WHERE a.proc_ver_id = c.id
		and b.id = c.process_id
		and e.new_state = d.id
		and d.code = p_code 
		and b.code = p_proc_code
		and c.start_dt <= p_req_dt
		and c.end_dt >= p_req_dt
		and a.doc_id = p_doc_id
		and a.id = e.request_id
		order by e.id desc 
		LIMIT 1
	);
	RETURN status_dt;
END //
DELIMITER ;

DELIMITER //
DROP FUNCTION IF EXISTS ActionPerson //
CREATE FUNCTION ActionPerson(p_proc_code varchar(15), p_doc_id int unsigned, p_req_dt datetime, p_code char(2)) RETURNS varchar(30)
BEGIN
	DECLARE action_user varchar(30);
	SET action_user = (
		SELECT e.username
		FROM wf_request a, wf_process b, wf_process_version c, wf_state d, wf_request_resp_user e
		WHERE a.proc_ver_id = c.id
		and b.id = c.process_id
		and e.current_state = d.id
		and d.code = p_code 
		and b.code = p_proc_code
		and c.start_dt <= p_req_dt
		and c.end_dt >= p_req_dt
		and a.doc_id = p_doc_id
		and a.id = e.request_id
		and e.status = 'C'
		order by e.id desc 
		LIMIT 1
	);
	RETURN action_user;
END //
DELIMITER ;

DROP TABLE IF EXISTS wf_user;
CREATE TABLE wf_user(
	id int unsigned not null auto_increment primary key,
	name varchar(255) not null,
	lcd timestamp default CURRENT_TIMESTAMP,
	lud timestamp default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS wf_process_admin;
CREATE TABLE wf_process_admin(
	process_id int unsigned not null,
	user_id int unsigned not null,
	lcd timestamp default CURRENT_TIMESTAMP,
	lud timestamp default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
	
DROP TABLE IF EXISTS wf_group;
CREATE TABLE wf_group(
	id int unsigned not null auto_increment primary key,
	process_id int unsigned not null,
	name varchar(255) not null,
	lcd timestamp default CURRENT_TIMESTAMP,
	lud timestamp default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS wf_group_member;
CREATE TABLE wf_group_member(
	group_id int unsigned not null,
	user_id int unsigned not null,
	lcd timestamp default CURRENT_TIMESTAMP,
	lud timestamp default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS wf_state;
CREATE TABLE wf_state(
	id int unsigned not null auto_increment primary key,
	state_type int unsigned not null,
	process_id int unsigned not null,
	name varchar(255),
	lcd timestamp default CURRENT_TIMESTAMP,
	lud timestamp default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
	
DROP TABLE IF EXISTS wf_action_type;
CREATE TABLE wf_action_type(
	id int unsigned not null auto_increment primary key,
	name varchar(255) not null,
	lcd timestamp default CURRENT_TIMESTAMP,
	lud timestamp default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
INSERT INTO wf_action_type(name) VALUES
('Approve'),
('Deny'),
('Cancel'),
('Restart'),
('Resolve')
;

DROP TABLE IF EXISTS wf_action;
CREATE TABLE wf_action(
	id int unsigned not null auto_increment primary key,
	action_type_id int unsigned not null,
	process_id int unsigned not null,
	name varchar(255),
	lcd timestamp default CURRENT_TIMESTAMP,
	lud timestamp default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS wf_transition_action;
CREATE TABLE wf_transition_action(
	transition_id int unsigned not null,
	action_id int unsigned not null,
	lcd timestamp default CURRENT_TIMESTAMP,
	lud timestamp default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS wf_activity_type;
CREATE TABLE wf_activity_type(
	id int unsigned not null auto_increment primary key,
	name varchar(255) not null,
	lcd timestamp default CURRENT_TIMESTAMP,
	lud timestamp default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
INSERT INTO wf_activity_type(name) VALUES
('Add Note'),
('Send Email'),
('Add Stakeholders'),
('Remove Stakeholders')
;

DROP TABLE IF EXISTS wf_activity;
CREATE TABLE wf_activity(
	id int unsigned not null auto_increment primary key,
	activity_type_id int unsigned not null,
	process_id int unsigned not null,
	name varchar(255),
	lcd timestamp default CURRENT_TIMESTAMP,
	lud timestamp default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS wf_target;
CREATE TABLE wf_target(
	id int unsigned not null auto_increment primary key,
	name varchar(255) not null,
	lcd timestamp default CURRENT_TIMESTAMP,
	lud timestamp default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
INSERT INTO wf_target(name) VALUES
('Requester'),
('Stakeholders'),
('Group Members'),
('Process Admins')
;

DROP TABLE IF EXISTS wf_activity_target;
CREATE TABLE wf_activity_target(
	id int unsigned not null auto_increment primary key,
	activity_type_id int unsigned not null,
	activity_id int unsigned not null,
	target_id int unsigned not null,
	group_id int unsigned not null,
	lcd timestamp default CURRENT_TIMESTAMP,
	lud timestamp default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS wf_request_action;
CREATE TABLE wf_request_action(
	id int unsigned not null auto_increment primary key,
	request_id int unsigned not null,
	action_id int unsigned not null,
	transition_id int unsigned not null,
	is_active tinyint unsigned not null,
	is_complete tinyint unsigned not null,
	lcd timestamp default CURRENT_TIMESTAMP,
	lud timestamp default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
