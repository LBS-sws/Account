DELIMITER //
DROP PROCEDURE IF EXISTS CopyWorkflow //
CREATE PROCEDURE CopyWorkflow(p_from_ver_id int unsigned, p_to_ver_id int unsigned)
BEGIN
  DELETE FROM wf_action_task WHERE action_id IN (
    SELECT id FROM wf_action WHERE proc_ver_id = p_to_ver_id
  );
  
  DELETE FROM wf_action WHERE proc_ver_id = p_to_ver_id;
  
  DELETE FROM wf_state WHERE proc_ver_id = p_to_ver_id;
  
  DELETE FROM wf_task WHERE proc_ver_id = p_to_ver_id;
  
  DELETE FROM wf_transition WHERE proc_ver_id = p_to_ver_id;
  
  INSERT INTO wf_action(proc_ver_id, code, name)
    SELECT p_to_ver_id, code, name
	FROM wf_action
	WHERE proc_ver_id = p_from_ver_id;
  
  INSERT INTO wf_state(proc_ver_id, code, name)
    SELECT p_to_ver_id, code, name
	FROM wf_state
	WHERE proc_ver_id = p_from_ver_id;
	
  INSERT INTO wf_task(proc_ver_id, name, function_call, param)
    SELECT p_to_ver_id, name, function_call, param
	FROM wf_task
	WHERE proc_ver_id = p_from_ver_id;
  
  INSERT INTO wf_transition(proc_ver_id, current_state, next_state)
    SELECT p_to_ver_id, d.id, e.id
	FROM wf_transition a, wf_state b, wf_state c, wf_state d, wf_state e
	WHERE a.proc_ver_id = p_from_ver_id 
	AND a.current_state = b.id 
	AND a.next_state = c.id
	AND b.code = d.code AND d.proc_ver_id = p_to_ver_id
	AND c.code = e.code AND e.proc_ver_id = p_to_ver_id
	;
  
  INSERT INTO wf_action_task(action_id, task_id, seq_no)
    SELECT c.id, e.id, a.seq_no
	FROM wf_action_task a, wf_action b, wf_action c, wf_task d, wf_task e
	WHERE a.action_id IN (SELECT x.id FROM wf_action x WHERE x.proc_ver_id = p_from_ver_id)
	AND a.action_id = b.id 
	AND b.code = c.code AND c.proc_ver_id = p_to_ver_id
	AND a.task_id = d.id
	AND d.name = e.name AND e.proc_ver_id = p_to_ver_id
	AND d.function_call = e.function_call AND d.param = e.param
    ;
END //
DELIMITER ;

DELIMITER //
DROP PROCEDURE IF EXISTS ShowTransition //
CREATE PROCEDURE ShowTransition(p_ver_id int unsigned)
BEGIN
  SELECT a.proc_ver_id, b.code, b.name, c.code, c.name
  FROM wf_transition a, wf_state b, wf_state c
  WHERE a.proc_ver_id = p_ver_id
  AND a.current_state = b.id 
  AND a.next_state = c.id
  ORDER BY a.current_state, a.next_state
  ;
END //
DELIMITER ;

DELIMITER //
DROP PROCEDURE IF EXISTS ShowActionTask //
CREATE PROCEDURE ShowActionTask(p_ver_id int unsigned)
BEGIN
  SELECT b.code, b.name, a.seq_no, c.name, c.function_call, c.param
  FROM wf_action_task a, wf_action b, wf_task c
  WHERE a.action_id = b.id AND b.proc_ver_id = p_ver_id
  AND a.task_id = c.id AND c.proc_ver_id = p_ver_id
  ORDER BY b.code, a.seq_no
  ;
END //
DELIMITER ;

DELIMITER //
DROP PROCEDURE IF EXISTS addAction //
CREATE PROCEDURE addAction(p_ver_id int unsigned, p_code varchar(15), p_name varchar(255))
BEGIN
  INSERT INTO wf_action(proc_ver_id, code, name)
  VALUES (p_ver_id, p_code, p_name);
  
  SELECT * FROM wf_action WHERE id = LAST_INSERT_ID();
END //
DELIMITER ;

DELIMITER //
DROP PROCEDURE IF EXISTS addState //
CREATE PROCEDURE addState(p_ver_id int unsigned, p_code varchar(15), p_name varchar(255))
BEGIN
  INSERT INTO wf_state(proc_ver_id, code, name)
  VALUES (p_ver_id, p_code, p_name);
  
  SELECT * FROM wf_state WHERE id = LAST_INSERT_ID();
END //
DELIMITER ;

DELIMITER //
DROP PROCEDURE IF EXISTS addTask //
CREATE PROCEDURE addTask(p_ver_id int unsigned, p_name varchar(255), p_function varchar(255), p_param varchar(1000))
BEGIN
  INSERT INTO wf_task(proc_ver_id, name, function_call, param)
  VALUES (p_ver_id, p_name, p_function, p_param);
  
  SELECT * FROM wf_task WHERE id = LAST_INSERT_ID();
END //
DELIMITER ;

DELIMITER //
DROP PROCEDURE IF EXISTS addTransition //
CREATE PROCEDURE addTransition(p_ver_id int unsigned, p_from_state varchar(15), p_to_state varchar(15))
BEGIN
  DECLARE from_state_id int unsigned;
  DECLARE to_state_id int unsigned;

  SET from_state_id = (SELECT id FROM wf_state WHERE proc_ver_id=p_ver_id AND code=p_from_state);
  SET to_state_id = (SELECT id FROM wf_state WHERE proc_ver_id=p_ver_id AND code=p_to_state);

  IF NOT EXISTS(SELECT id FROM wf_transition WHERE proc_ver_id=p_ver_id AND current_state=from_state_id AND next_state=to_state_id) THEN
    INSERT INTO wf_transition(proc_ver_id, current_state, next_state)
    VALUES (p_ver_id, from_state_id, to_state_id);
  END IF;

  SELECT * FROM wf_transition WHERE id = LAST_INSERT_ID();
END //
DELIMITER ;

DELIMITER //
DROP PROCEDURE IF EXISTS addActionTask //
CREATE PROCEDURE addActionTask(p_ver_id int unsigned, p_action varchar(15), p_seq int unsigned, p_task varchar(255))
BEGIN
  DECLARE p_action_id int unsigned;
  DECLARE p_task_id int unsigned;

  SET p_action_id = (SELECT id FROM wf_action WHERE proc_ver_id=p_ver_id AND code=p_action);
  SET p_task_id = (SELECT id FROM wf_task WHERE proc_ver_id=p_ver_id AND name=p_task);

  IF EXISTS(SELECT action_id FROM wf_action_task WHERE action_id=p_action_id AND seq_no=p_seq) THEN
    UPDATE wf_action_task SET task_id = p_task_id WHERE action_id=p_action_id AND seq_no=p_seq;
  ELSE
    INSERT INTO wf_action_task(action_id, seq_no, task_id)
    VALUES (p_action_id, p_seq, p_task_id);
  END IF;

  SELECT * FROM wf_action_task WHERE action_id=p_action_id AND seq_no=p_seq;
END //
DELIMITER ;

