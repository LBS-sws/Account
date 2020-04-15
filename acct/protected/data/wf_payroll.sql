INSERT INTO wf_process(id, code, name) values(3, 'PAYROLL', 'Payroll Approval Process');

INSERT INTO wf_process_version(id, process_id, start_dt, end_dt) values(5, 3, '2020-01-01', '2099-12-31');

INSERT INTO wf_action(id, proc_ver_id, code, name) values
(301,5,'APPROVE','主管批准工资表'),
(302,5,'DENY','主管拒绝工资表'),
(303,5,'SUBMIT','提交工资表'),
(304,5,'RESUBMIT','再提交工资表'),
(305,5,'RHAPPROVE','总监批准工资表'),
(306,5,'RHDENY','总监拒绝工资表'),
(307,5,'RDAPPROVE','副总监批准工资表'),
(308,5,'RDDENY','副总监拒绝工资表')
;

INSERT INTO wf_task(id, proc_ver_id, name, function_call, param) values
(301,5,'Send Current State Email','sendEmail',''),
(302,5,'Send AA Email','sendEmail','AA'),
(303,5,'Send AB Email','sendEmail','AB'),
(304,5,'Send DA Email','sendEmail','DA'),
(305,5,'Send DB Email','sendEmail','DB'),
(306,5,'Denied Routing','route','denied'),
(307,5,'Accepted Routing','route','accepted'),
(308,5,'Routing','route',''),
(309,5,'Clear All Pending','clearAllPending',''),
(310,5,'Send AC Email','sendEmail','AC'),
(311,5,'Send DC Email','sendEmail','DC')
;

INSERT INTO wf_action_task(action_id, task_id, seq_no) values
(303, 308, 1),
(303, 301, 2),
(301, 309, 1),
(301, 307, 2),
(301, 303, 3),
(301, 301, 4),
(302, 309, 1),
(302, 306, 2),
(302, 305, 3),
(302, 301, 4),
(304, 308, 1),
(304, 301, 2),
(305, 309, 1),
(305, 307, 2),
(305, 302, 3),
(306, 309, 1),
(306, 306, 2),
(306, 304, 3),
(306, 301, 4),
(307, 309, 1),
(307, 307, 2),
(307, 310, 3),
(307, 301, 4),
(308, 309, 1),
(308, 306, 2),
(308, 311, 3),
(308, 301, 4)
;

INSERT INTO wf_state(id, proc_ver_id, code, name) VALUES
(301,5,'ST','开始'),
(302,5,'ED','结束'),
(303,5,'PA','有待总监审核'),
(304,5,'PS','有待再提交'),
(305,5,'AA','总监已批准'),
(306,5,'DA','总监已拒绝'),
(307,5,'PB','有待主管审核'),
(308,5,'AB','主管已批准'),
(309,5,'DB','主管已拒绝'),
(310,5,'P1','有待主管再审核'),
(311,5,'AC','副总监已批准'),
(312,5,'DC','副总监已拒绝'),
(313,5,'PC','有待副总监审核'),
(314,5,'P2','有待副总监再审核')
;

alter table wf_transition add column state_cond varchar(1000) default '' after next_state;
alter table wf_transition add column resp_party varchar(1000) default '' after state_cond;
INSERT INTO wf_transition(proc_ver_id, current_state, next_state, state_cond, resp_party) VALUES
(5,301,307,'hasLevelOne','toManager'),
(5,301,313,'!hasLevelOne && hasLevelTwo','toADirector'),
(5,301,303,'!hasLevelOne && !hasLevelTwo','toDirector'),
(5,307,308,'',''),
(5,307,309,'',''),
(5,308,303,'!hasLevelTwo','toDirector'),
(5,308,313,'hasLevelTwo','toADirector'),
(5,309,304,'','toRequestor'),
(5,304,307,'hasLevelOne','toManager'),
(5,304,313,'!hasLevelOne && hasLevelTwo','toADirector'),
(5,304,303,'!hasLevelOne && !hasLevelTwo','toDirector'),
(5,303,305,'',''),
(5,303,306,'',''),
(5,305,302,'',''),
(5,306,314,'hasLevelTwo','toADirector'),
(5,306,310,'hasLevelOne && !hasLevelTwo','toManager'),
(5,306,304,'!hasLevelOne && !hasLevelTwo','toRequestor'),
(5,310,308,'',''),
(5,310,309,'',''),
(5,313,311,'',''),
(5,313,312,'',''),
(5,311,303,'','toDirector'),
(5,312,310,'hasLevelOne','toManager'),
(5,312,304,'!hasLevelOne','toRequestor'),
(5,314,311,'',''),
(5,314,312,'','')
;

