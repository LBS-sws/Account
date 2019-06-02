<?php

class SystemNotice 
{
	public static function addNotice(&$connection, $params = array()) {
		if (empty($params)) return false;

		$userarray = is_array($params['username']) ? $params['username'] : json_decode($params['username']);
		if (empty($userarray)) return false;
		$finalusers = array();
		foreach ($userarray as $user) {
			if (!in_array($user, $finalusers)) $finalusers[] = $user;
		}

		$suffix = Yii::app()->params['envSuffix'];
		$sql = "insert into swoper$suffix.swo_notification
					(system_id, note_type, subject, description, message, lcu, luu)
				values
					(:system_id, :note_type, :subject, :description, :message, 'admin', 'admin')
			";
		$command = $connection->createCommand($sql);
		if (strpos($sql,':system_id')!==false)
			$command->bindParam(':system_id',$params['system_id'],PDO::PARAM_STR);
		if (strpos($sql,':note_type')!==false) {
			$note_type = $params['note_type']=='action' ? 'ACTN' : ($params['note_type']=='notice' ? 'NOTI' : $params['note_type']);
			$command->bindParam(':note_type',$note_type,PDO::PARAM_STR);
		}
		if (strpos($sql,':subject')!==false)
			$command->bindParam(':subject',$params['subject'],PDO::PARAM_STR);
		if (strpos($sql,':description')!==false)
			$command->bindParam(':description',$params['description'],PDO::PARAM_STR);
		if (strpos($sql,':message')!==false)
			$command->bindParam(':message',$params['message'],PDO::PARAM_STR);
		$command->execute();
		$note_id = Yii::app()->db->getLastInsertID();

		foreach ($finalusers as $user) {
			$sql1 = "insert into swoper$suffix.swo_notification_user
						(note_id, username, status, lcu, luu)
					values
						(:note_id, :username, :status, 'admin', 'admin')
				";
			$command = $connection->createCommand($sql1);
			if (strpos($sql1,':note_id')!==false)
				$command->bindParam(':note_id',$note_id,PDO::PARAM_INT);
			if (strpos($sql1,':username')!==false)
				$command->bindParam(':username',$user,PDO::PARAM_STR);
			if (strpos($sql1,':status')!==false) {
				$status = $params['note_type']=='action' ||  $params['note_type']=='ACTN' ? 'S' : 'N';
				$command->bindParam(':status',$status,PDO::PARAM_STR);
			}
			$command->execute();

			if ($params['note_type']=='action' || $params['note_type']=='ACTN') {
				$sql2 = "insert into swoper$suffix.swo_notification_action
							(note_id, username, form_id, rec_id, status, lcu, luu)
						values
							(:note_id, :username, :form_id, :rec_id, 'N', 'admin', 'admin')
					";
				$command = $connection->createCommand($sql2);
				if (strpos($sql2,':note_id')!==false)
					$command->bindParam(':note_id',$note_id,PDO::PARAM_INT);
				if (strpos($sql2,':username')!==false)
					$command->bindParam(':username',$user,PDO::PARAM_STR);
				if (strpos($sql2,':form_id')!==false)
					$command->bindParam(':form_id',$params['form_id'],PDO::PARAM_STR);
				if (strpos($sql2,':rec_id')!==false)
					$command->bindParam(':rec_id',$params['rec_id'],PDO::PARAM_INT);
				$command->execute();
			}
		}
		
		return true;
	}
	
	public static function markRead($formid, $recid) {
		$user = Yii::app()->user->id;
		$suffix = Yii::app()->params['envSuffix'];
		$sysid = Yii::app()->params['systemId'];
		$sql = "update 
					swoper$suffix.swo_notification_action a, 
					swoper$suffix.swo_notification_user b,
					swoper$suffix.swo_notification c
				set 
					a.status='Y', a.luu=:uid, b.status='C', b.luu=:uid
				where 
					a.form_id=:formid and a.rec_id=:recid and a.username=:uid and
					a.note_id=c.id and b.note_id=c.id and a.username=b.username and
					c.system_id=:sysid and a.status='N'
			";
		$command = Yii::app()->db->createCommand($sql);
		if (strpos($sql,':uid')!==false)
			$command->bindParam(':uid',$user,PDO::PARAM_STR);
		if (strpos($sql,':formid')!==false)
			$command->bindParam(':formid',$formid,PDO::PARAM_STR);
		if (strpos($sql,':recid')!==false)
			$command->bindParam(':recid',$recid,PDO::PARAM_INT);
		if (strpos($sql,':sysid')!==false)
			$command->bindParam(':sysid',$sysid,PDO::PARAM_STR);
		$command->execute();
	}
	
	public static function markReadforAllUser($formid, $recid) {
		$user = Yii::app()->user->id;
		$suffix = Yii::app()->params['envSuffix'];
		$sysid = Yii::app()->params['systemId'];
		$sql = "update 
					swoper$suffix.swo_notification_action a, 
					swoper$suffix.swo_notification_user b,
					swoper$suffix.swo_notification c
				set 
					a.status='Y', a.luu=:uid, b.status='C', b.luu=:uid
				where 
					a.form_id=:formid and a.rec_id=:recid and
					a.note_id=c.id and b.note_id=c.id and a.username=b.username and
					c.system_id=:sysid and a.status='N'
			";
		$command = Yii::app()->db->createCommand($sql);
		if (strpos($sql,':uid')!==false)
			$command->bindParam(':uid',$user,PDO::PARAM_STR);
		if (strpos($sql,':formid')!==false)
			$command->bindParam(':formid',$formid,PDO::PARAM_STR);
		if (strpos($sql,':recid')!==false)
			$command->bindParam(':recid',$recid,PDO::PARAM_INT);
		if (strpos($sql,':sysid')!==false)
			$command->bindParam(':sysid',$sysid,PDO::PARAM_STR);
		$command->execute();
	}
}

?>