<?php
class JobQueueCommand extends CConsoleCommand {
	protected $webroot;
	
	public function run($args) {
		$this->webroot = Yii::app()->params['webroot'];
		$sql = "select a.id, a.ts, a.rpt_type, a.username, a.rpt_desc, a.req_dt  
					from acc_queue a
				where a.status='P' order by a.req_dt limit 1";
		$row = Yii::app()->db->createCommand($sql)->queryRow();
		if ($row===false) return;
		
		$id = $row['id'];
		$ts = $row['ts'];
		$format = empty($row['rpt_type']) ? 'EXCEL' : $row['rpt_type'];
		$uid = $row['username'];
		
		if ($id!=0) $param = $this->getQueueParam($id);
		
		if (($id!=0) && !empty($param) && $this->markStatus($id, $ts, 'I')) {
			if (isset($param['LANGUAGE'])) Yii::app()->language = $param['LANGUAGE'];
			$param['UID'] = $uid;
			$param['REQ_DT'] = $row['req_dt'];
			$param['RPT_DESC'] = $row['rpt_desc'];
			$ts = $this->getTimeStamp($id);
				
			$rpt_desc = $param['RPT_DESC'];
			$mesg = "ID:$id NAME:$rpt_desc FORMAT:$format USER:$uid\n";
			
			try {
				$out = $this->genReport($param['RPT_ID'], $param, $format);
			} catch(Exception $e) {
				$mesg .= $e->getMessage()."\n";
				$out = '';
			}
			
			if (!empty($out)) {
				$this->saveOutput($id, $ts, $out, 'C');
				echo $mesg;
				echo "\t-Done (default)\n";
			} else {
					$this->markStatus($id, $ts, 'F');
					echo $mesg;
					echo "\t-FAIL\n";
			}
		}
	}
	
	protected function updateQueueUser($id, $users) {
		if (!empty($users)) {
			foreach ($users as $username) {
				$sql = "select id from acc_queue_user where queue_id=$id and username='$username' limit 1";
				if (Yii::app()->db->createCommand($sql)->queryRow()===false) {
					$sql = "insert into acc_queue_user (queue_id, username)
							values(:queue_id, :username)
					";

					$command=Yii::app()->db->createCommand($sql);
					if (strpos($sql,':queue_id')!==false)
						$command->bindParam(':queue_id',$id,PDO::PARAM_INT);
					if (strpos($sql,':username')!==false)
						$command->bindParam(':username',$username,PDO::PARAM_STR);
					$command->execute();
				}
			}
		}
	}
	
	protected function getQueueParam($qid) {
		$rtn = array();
		$sql = "select * from acc_queue_param where queue_id=".$qid;
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
		$sql = "update acc_queue set status=:status where id=:id and ts=:ts";
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
	
	protected function saveOutput($id, $ts, $outstring, $sts) {
		try {
			$sql = "update acc_queue set status=:sts, fin_dt=now(), rpt_content=:content where id=:id and ts=:ts";
			$command=Yii::app()->db->createCommand($sql);
			if (strpos($sql,':id')!==false)
				$command->bindParam(':id',$id,PDO::PARAM_INT);
			if (strpos($sql,':content')!==false)
				$command->bindParam(':content',$outstring,PDO::PARAM_LOB);
			if (strpos($sql,':ts')!==false)
				$command->bindParam(':ts',$ts,PDO::PARAM_STR);
			if (strpos($sql,':sts')!==false)
				$command->bindParam(':sts',$sts,PDO::PARAM_STR);
			$cnt = $command->execute();
		}
		catch(Exception $e) {
			throw new CDbException($e->getMessage(),$e->getCode());
		}
		return ($cnt>0);
	}
	
	protected function getTimeStamp($id) {
		$ts = '';
		$sql = "select ts from acc_queue where id=".$id;
		$rows = Yii::app()->db->createCommand($sql)->queryAll();
		if (count($rows) > 0) {
			foreach ($rows as $row) {
				$ts = $row['ts'];
				break;
			}
		}
		return $ts;
	}

	protected function genReport($rptid, $param, $format) {
		$report = new $rptid();
		$report->criteria = $param;
		$output = $report->genReport();
		return $output;
	}

	protected function getCityName($code) {
		$suffix = Yii::app()->params['envSuffix'];
		$sql = "select name from security$suffix.sec_city where code='$code'";
		return Yii::app()->db->createCommand($sql)->queryScalar();
	}
}
?>