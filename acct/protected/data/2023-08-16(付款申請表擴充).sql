
-- ----------------------------
-- Table structure for acc_request
-- ----------------------------
ALTER TABLE acc_request ADD COLUMN doc_count_req int(3) NOT NULL DEFAULT 0 COMMENT '附件的總數量' AFTER status;
ALTER TABLE acc_request ADD COLUMN doc_count_real int(3) NOT NULL DEFAULT 0 COMMENT 'real的總數量' AFTER status;
ALTER TABLE acc_request ADD COLUMN doc_count_tax int(3) NOT NULL DEFAULT 0 COMMENT '稅票的總數量' AFTER status;

UPDATE accountdev.acc_request
SET doc_count_req = docmandev.countdoc ('payreq', id),
 doc_count_real = docmandev.countdoc ('payreal', id),
 doc_count_tax = docmandev.countdoc ('tax', id)
WHERE id>0