INSERT INTO `wf_state` (`id`, `proc_ver_id`, `code`, `name`, `lcd`, `lud`) VALUES ('400', '5', 'PD', '有待高级总经理审核', '2024-01-11 18:25:43', '2024-01-11 12:26:54');
INSERT INTO `wf_state` (`id`, `proc_ver_id`, `code`, `name`, `lcd`, `lud`) VALUES ('401', '5', 'PE', '有待高级总经理/副总监审核', '2024-01-11 18:25:43', '2024-01-11 12:26:57');
INSERT INTO `wf_state` (`id`, `proc_ver_id`, `code`, `name`, `lcd`, `lud`) VALUES ('402', '5', 'AD', '高级总经理已批准', '2024-01-11 18:25:43', '2024-01-11 12:27:25');
INSERT INTO `wf_state` (`id`, `proc_ver_id`, `code`, `name`, `lcd`, `lud`) VALUES ('403', '5', 'DD', '高级总经理已拒绝', '2024-01-11 18:25:43', '2024-01-11 12:27:27');
INSERT INTO `wf_state` (`id`, `proc_ver_id`, `code`, `name`, `lcd`, `lud`) VALUES ('404', '5', 'P3', '有待高级总经理再审核', '2024-01-11 18:25:43', '2024-01-11 12:27:32');
INSERT INTO `wf_state` (`id`, `proc_ver_id`, `code`, `name`, `lcd`, `lud`) VALUES ('405', '5', 'P4', '有待高级总经理/副总监再审核', '2024-01-11 18:25:43', '2024-01-11 12:27:35');

INSERT INTO `wf_transition` (`id`, `proc_ver_id`, `current_state`, `next_state`, `state_cond`, `resp_party`, `lcd`, `lud`) VALUES ('200', '5', '402', '303', '', 'toDirector', '2024-01-11 08:28:47', '2024-01-11 12:25:55');
INSERT INTO `wf_transition` (`id`, `proc_ver_id`, `current_state`, `next_state`, `state_cond`, `resp_party`, `lcd`, `lud`) VALUES ('201', '5', '403', '310', 'hasLevelOne', 'toManager', '2024-01-11 08:28:47', '2024-01-11 12:26:00');
INSERT INTO `wf_transition` (`id`, `proc_ver_id`, `current_state`, `next_state`, `state_cond`, `resp_party`, `lcd`, `lud`) VALUES ('202', '5', '403', '304', '!hasLevelOne', 'toRequestor', '2024-01-11 08:28:47', '2024-01-11 12:26:04');
INSERT INTO `wf_transition` (`id`, `proc_ver_id`, `current_state`, `next_state`, `state_cond`, `resp_party`, `lcd`, `lud`) VALUES ('203', '5', '404', '402', '', '', '2024-01-11 08:28:47', '2024-01-11 12:26:06');
INSERT INTO `wf_transition` (`id`, `proc_ver_id`, `current_state`, `next_state`, `state_cond`, `resp_party`, `lcd`, `lud`) VALUES ('204', '5', '404', '403', '', '', '2024-01-11 08:28:47', '2024-01-11 12:26:09');
INSERT INTO `wf_transition` (`id`, `proc_ver_id`, `current_state`, `next_state`, `state_cond`, `resp_party`, `lcd`, `lud`) VALUES ('205', '5', '400', '402', '', '', '2024-01-11 08:28:47', '2024-01-11 12:26:11');
INSERT INTO `wf_transition` (`id`, `proc_ver_id`, `current_state`, `next_state`, `state_cond`, `resp_party`, `lcd`, `lud`) VALUES ('206', '5', '400', '403', '', '', '2024-01-11 08:28:47', '2024-01-11 12:26:13');
INSERT INTO `wf_transition` (`id`, `proc_ver_id`, `current_state`, `next_state`, `state_cond`, `resp_party`, `lcd`, `lud`) VALUES ('207', '5', '401', '311', '', '', '2024-01-11 08:28:47', '2024-01-11 12:26:16');
INSERT INTO `wf_transition` (`id`, `proc_ver_id`, `current_state`, `next_state`, `state_cond`, `resp_party`, `lcd`, `lud`) VALUES ('208', '5', '401', '312', '', '', '2024-01-11 08:28:47', '2024-01-11 12:26:18');
INSERT INTO `wf_transition` (`id`, `proc_ver_id`, `current_state`, `next_state`, `state_cond`, `resp_party`, `lcd`, `lud`) VALUES ('209', '5', '308', '400', 'hasHeight', 'toADirector', '2024-01-11 08:28:47', '2024-01-11 12:26:21');
INSERT INTO `wf_transition` (`id`, `proc_ver_id`, `current_state`, `next_state`, `state_cond`, `resp_party`, `lcd`, `lud`) VALUES ('210', '5', '308', '401', 'hasTwoPeople', 'toADirector', '2024-01-11 08:28:47', '2024-01-11 12:26:23');
INSERT INTO `wf_transition` (`id`, `proc_ver_id`, `current_state`, `next_state`, `state_cond`, `resp_party`, `lcd`, `lud`) VALUES ('211', '5', '306', '404', 'hasHeight', 'toADirector', '2024-01-11 08:28:47', '2024-01-11 12:26:25');
INSERT INTO `wf_transition` (`id`, `proc_ver_id`, `current_state`, `next_state`, `state_cond`, `resp_party`, `lcd`, `lud`) VALUES ('212', '5', '306', '405', 'hasTwoPeople', 'toADirector', '2024-01-11 08:28:47', '2024-01-11 12:26:28');
INSERT INTO `wf_transition` (`id`, `proc_ver_id`, `current_state`, `next_state`, `state_cond`, `resp_party`, `lcd`, `lud`) VALUES ('213', '5', '405', '311', '', '', '2024-01-11 08:28:47', '2024-01-11 12:26:06');
INSERT INTO `wf_transition` (`id`, `proc_ver_id`, `current_state`, `next_state`, `state_cond`, `resp_party`, `lcd`, `lud`) VALUES ('214', '5', '405', '312', '', '', '2024-01-11 08:28:47', '2024-01-11 12:26:09');
INSERT INTO `wf_transition` (`id`, `proc_ver_id`, `current_state`, `next_state`, `state_cond`, `resp_party`, `lcd`, `lud`) VALUES ('215', '5', '401', '402', '', '', '2024-01-11 08:28:47', '2024-01-11 12:26:11');
INSERT INTO `wf_transition` (`id`, `proc_ver_id`, `current_state`, `next_state`, `state_cond`, `resp_party`, `lcd`, `lud`) VALUES ('216', '5', '401', '403', '', '', '2024-01-11 08:28:47', '2024-01-11 12:26:13');

update wf_transition set state_cond='hasNoHeight' where state_cond='hasLevelTwo' and proc_ver_id=5 and  current_state in (308,306);
update wf_transition set state_cond='!auditIsHeight' where proc_ver_id=5 and  current_state =401 and  next_state in (311,312);
