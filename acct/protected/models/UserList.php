<?php

class UserList extends CListPageModel
{
	/**
	 * Declares customized attribute labels.
	 * If not declared here, an attribute would have a label that is
	 * the same as its name with the first letter in upper case.
	 */
	public function attributeLabels()
	{
		return array(	
			'username'=>Yii::t('user','User ID'),
			'disp_name'=>Yii::t('user','Display Name'),
			'group_name'=>Yii::t('user','Group Name'),
			'logon_time'=>Yii::t('user','Last Logon'),
			'logoff_time'=>Yii::t('user','Last Logoff'),
			'status'=>Yii::t('user','Status'),
			'city'=>Yii::t('user','City'),
		);
	}
	
	public function retrieveDataByPage($pageNum=1)
	{
		$suffix = Yii::app()->params['envSuffix'];

		$sql1 = "select a.username, a.disp_name, a.logon_time, a.logoff_time, a.status, c.name as city
				from security$suffix.sec_user a 
				left outer join security$suffix.sec_city c
				on a.city = c.code 
				where a.username <> 'admin'  
			";
		$sql2 = "select count(a.username)
				from security$suffix.sec_user a 
				left outer join security$suffix.sec_city c
				on a.city = c.code 
				where a.username <> 'admin'  
			";
		$clause = "";
		if (!empty($this->searchField) && !empty($this->searchValue)) {
			$svalue = str_replace("'","\'",$this->searchValue);
			switch ($this->searchField) {
				case 'username':
					$clause .= General::getSqlConditionClause('a.username',$svalue);
					break;
				case 'disp_name':
					$clause .= General::getSqlConditionClause('a.disp_name',$svalue);
					break;
				case 'city':
					$clause .= General::getSqlConditionClause('c.name',$svalue);
					break;
			}
		}
		
		$order = "";
		if (!empty($this->orderField)) {
			$order .= " order by ".$this->orderField." ";
			if ($this->orderType=='D') $order .= "desc ";
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
					$this->attr[] = array(
						'username'=>$record['username'],
						'disp_name'=>$record['disp_name'],
						'logon_time'=>$record['logon_time'],
						'logoff_time'=>$record['logoff_time'],
						'status'=>General::getActiveStatusDesc($record['status']),
						'city'=>$record['city'],
					);
			}
		}
		$session = Yii::app()->session;
		$session['criteria_ul'] = $this->getCriteria();
		return true;
	}

}
