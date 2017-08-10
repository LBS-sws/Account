<?php
class ImportCommand extends CConsoleCommand {
	protected $webroot;
	protected $city;
	protected $uid;
	
	public function run($args) {
		$this->webroot = Yii::app()->params['webroot'];
		$sql = "select a.id, a.ts, a.import_type, a.username, a.class_name, a.req_dt, file_type, file_content   
					from acc_import_queue a
				where a.status='P' order by a.req_dt limit 1";
		$row = Yii::app()->db->createCommand($sql)->queryRow();
		if ($row===false) return;
		
		$id = $row['id'];
		$ts = $row['ts'];
		$this->uid = $row['username'];
		
		if ($id!=0) $param = $this->getQueueParam($id);
		
		if (($id!=0) && !empty($param) && $this->markStatus($id, $ts, 'I')) {
			$this->city = $param['CITY'];
			$mapping = json_decode($param['MAPPING']);
			$classname = $row['class_name'];
			$importtype = $row['import_type'];
			$uid = $this->uid;
			$ts = $this->getTimeStamp($id);
				
			$mesg = "ID:$id NAME:$importtype CLASS:$classname USER:$uid\n";
			echo $mesg;
	
			$excelfile = $this->writeExcelFile($row['file_content']);
			$data = $this->formatData($excelfile, $row['file_type'], $mapping);
			$log = $this->import($classname, $data, $id);
			
			$sts = (strpos($log, Yii::t('import','ERROR'))===false) ? 'C' : 'F';
			$this->markStatus($id, $ts, $sts);
			echo "\t-Done (default)\n";
		}
	}

	protected function import($classname, $data, $queueid) {
		$model = new $classname();
		$logmsgErr = '';
		$logmsgOk = '';
		$logmsg = '';
		$cnt = 0;
		
		$connection = Yii::app()->db;
		$transaction=$connection->beginTransaction();
		try {
			foreach ($data as $row) {
				$msgErr = $model->validateData($row);
				$msgOk = '';
				if ($msgErr=='') {
					$cnt++;
					$msgOk = $model->importData($connection, $row);
				}
				
				$logmsgErr .= (empty($logmsgErr) ? "" : (empty($msgErr) ? "" : "\n")).$msgErr;
				$logmsgOk .= (empty($logmsgOk) ? "" : (empty($msgOk) ? "" : "\n")).$msgOk;
				echo (empty($msgErr) ? $msgOk : $msgErr)."\n";
				
				if ($cnt == 500) {
					$logmsg = empty($logmsgErr) ? Yii::t('import','Import Success!') : Yii::t('import','Import Error:')."\n".$logmsgErr;
					$logmsg .= "\n\n".Yii::t('import','Imported Rows:')."\n".$logmsgOk;
					
					$this->saveLog($connection, $queueid, $logmsg);
					$transaction->commit();
					$transaction=$connection->beginTransaction();
					$cnt = 0;
				}
			}
			$logmsg = empty($logmsgErr) ? Yii::t('import','Import Success!') : Yii::t('import','Import Error:')."\n".$logmsgErr;
			$logmsg .= "\n\n".Yii::t('import','Imported Rows:')."\n".$logmsgOk;
			$this->saveLog($connection, $queueid, $logmsg);
			$transaction->commit();

		} catch(Exception $e) {
			$transaction->rollback();
			echo 'Error: '.$e->getMessage();
			$logmsg .= "\n\n".Yii::t('import','ERROR').' '.$e->getMessage();
		}
		return $logmsg;
	}
	
	protected function writeExcelFile($content) {
		$file = tempnam(sys_get_temp_dir(), 'excel_');
		$handle = fopen($file, "w");
		fwrite($handle, $content);
		fclose($handle);
		return $file;
	}
	
	protected function formatData($filename, $fileext, $mapping) {
		$rtn = array();
		
		$excel = new ExcelTool();
		$excel->start();
		$readerType = strtolower($fileext)=='xlsx' ? 'Excel2007' : 'Excel5';
		$excel->readFileByType($filename, $readerType);
		
		$emptycnt = 0;
		$rowidx = 2;
		$ws = $excel->setActiveSheet(0);
		do {
			$fields = array();
			$emptyrow = true;
			foreach ($mapping as $item) {
				if ($item->filefield >= 0) {
					$value = $excel->getCellValue($excel->getColumn($item->filefield),$rowidx); 
					$fields[$item->dbfieldid] = $value;
					if ($emptyrow && !empty($value)) $emptyrow = false;
				}
			}
			if ($emptyrow) {
				$emptycnt++;
			} else {
				if (!isset($fields['uid'])) $fields['uid'] = $this->uid;
				if (!isset($fields['city'])) $fields['city'] = $this->city;
				if (!isset($fields['excel_row'])) $fields['excel_row'] = $rowidx;
				$rtn[] = $fields;
				$emptycnt = 0;
			}
			$rowidx++;
		} while ($emptycnt <= 2);
		
		$excel->end();
		unlink($filename);
		
		return $rtn;
	}
	
	protected function getQueueParam($qid) {
		$rtn = array();
		$sql = "select * from acc_import_queue_param where queue_id=".$qid;
		$rows = Yii::app()->db->createCommand($sql)->queryAll();
		if (count($rows) > 0) {
			foreach ($rows as $row) {
				$param_field = $row['param_field'];
				$param_value = $row['param_value'];
				$rtn[$param_field] = $param_value; 
			}
		}
		return $rtn;
	}
	
	protected function markStatus($id, $ts, $sts) {
		$sql = ($sts=='C' || $sts=='F')
			? "update acc_import_queue set status=:status, fin_dt=now() where id=:id and ts=:ts"
			: "update acc_import_queue set status=:status where id=:id and ts=:ts";
		$command=Yii::app()->db->createCommand($sql);
		if (strpos($sql,':id')!==false)
			$command->bindParam(':id',$id,PDO::PARAM_INT);
		if (strpos($sql,':status')!==false)
			$command->bindParam(':status',$sts,PDO::PARAM_STR);
		if (strpos($sql,':ts')!==false)
			$command->bindParam(':ts',$ts,PDO::PARAM_STR);
		$cnt = $command->execute();
		return ($cnt>0);
	}
	
	protected function saveLog(&$connection, $id, $msg) {
		$sql = "update acc_import_queue_param set param_text=:msg where queue_id=:id and param_field='LOG'";
		$command=$connection->createCommand($sql);
		if (strpos($sql,':id')!==false)
			$command->bindParam(':id',$id,PDO::PARAM_INT);
		if (strpos($sql,':msg')!==false)
			$command->bindParam(':msg',$msg,PDO::PARAM_STR);
		$command->execute();
	}
	
	protected function getTimeStamp($id) {
		$ts = '';
		$sql = "select ts from acc_import_queue where id=".$id;
		$rows = Yii::app()->db->createCommand($sql)->queryAll();
		if (count($rows) > 0) {
			foreach ($rows as $row) {
				$ts = $row['ts'];
				break;
			}
		}
		return $ts;
	}
}
?>