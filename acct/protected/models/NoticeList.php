<?php

class NoticeList extends CListPageModel
{
	public function attributeLabels()
	{
		return array(
			'subject'=>Yii::t('queue','Subject'),
			'note_dt'=>Yii::t('queue','Date'),
			'note_type'=>Yii::t('queue','Type'),
			'status'=>Yii::t('queue','Status'),
			'id'=>Yii::t('queue','ID'),
		);
	}
	
	public function retrieveDataByPage($pageNum=1)
	{
		$uid = Yii::app()->user->id;
		$sysid = Yii::app()->params['systemId'];
		$suffix = Yii::app()->params['envSuffix'];
		$suffix = $suffix=='dev' ? '_w' : $suffix;
		$sql1 = "select a.*, b.status
				from swoper$suffix.swo_notification a, swoper$suffix.swo_notification_user b 
				where b.username='$uid' and a.system_id='$sysid'
				and a.id=b.note_id
			";
		$sql2 = "select count(a.id)
				from swoper$suffix.swo_notification a, swoper$suffix.swo_notification_user b 
				where b.username='$uid' and a.system_id='$sysid'
				and a.id=b.note_id
			";
		$clause = "";
		if (!empty($this->searchField) && !empty($this->searchValue)) {
			$svalue = str_replace("'","\'",$this->searchValue);
			switch ($this->searchField) {
				case 'subject':
					$clause .= General::getSqlConditionClause('a.subject',$svalue);
					break;
				case 'note_type':
					$field = "(select case a.note_type when 'ACTN' then '".Yii::t('queue','Action')."' 
							when 'NOTI' then '".Yii::t('queue','Notify')."' 
						end) ";
					$clause .= General::getSqlConditionClause($field, $svalue);
					break;
				case 'note_dt':
					$clause .= General::getSqlConditionClause('a.lcd',$svalue);
					break;
				case 'status':
					$field = "(select case b.status when 'N' then '".Yii::t('queue','Unread')."' 
							when 'S' then '".Yii::t('queue','Unread')."' 
							when 'C' then '".Yii::t('queue','Read')."' 
						end) ";
					$clause .= General::getSqlConditionClause($field, $svalue);
					break;
			}
		}
		
		$order = "";
		if (!empty($this->orderField)) {
			switch ($this->orderField) {
				case 'note_dt':
					$order .= " order by a.lcd ";
					break;
				default: 
					$order .= " order by ".$this->orderField." ";
			}
			if ($this->orderType=='D') $order .= "desc ";
		} else {
			$order .= " order by a.id desc ";
		}

		$sql = $sql2.$clause;
		$this->totalRow = Yii::app()->db->createCommand($sql)->queryScalar();
		
		$sql = $sql1.$clause.$order;
		$sql = $this->sqlWithPageCriteria($sql, $this->pageNum);
		$records = Yii::app()->db->createCommand($sql)->queryAll();
		
		$list = array();
		$this->attr = array();
		if (count($records) > 0) {
			foreach ($records as $k=>$record) {
				switch ($record['note_type']) {
					case 'ACTN': $type_name = Yii::t('queue','Action'); break;
					case 'NOTI': $type_name = Yii::t('queue','Notify'); break;
					default: $type_name = $record['note_type'];
				}
				$sts_name = $record['status']=='C' ? Yii::t('queue','Read') : Yii::t('queue','Unread');
				$this->attr[] = array(
					'id'=>$record['id'],
					'subject'=>$record['subject'],
					'note_dt'=>$record['lcd'],
					'note_type'=>$record['note_type'],
					'status'=>$sts_name,
					'note_type'=>$type_name,
				);
			}
		}
		$session = Yii::app()->session;
		$session[$this->criteriaName()] = $this->getCriteria();
		return true;
	}

	public function criteriaName() {
		return Yii::app()->params['systemId'].'_criteria_z101';
	}
}
