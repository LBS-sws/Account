<?php

class InvoiceEmailList extends CListPageModel
{
	/**
	 * Declares customized attribute labels.
	 * If not declared here, an attribute would have a label that is
	 * the same as its name with the first letter in upper case.
	 */
	public function attributeLabels()
	{
		return array(	
			'start_dt'=>Yii::t('code','effect date'),
			'email_text'=>Yii::t('queue','Email'),
			'remarks'=>Yii::t('code','Remarks'),
		);
	}
	
	public function retrieveDataByPage($pageNum=1)
	{
		$suffix = Yii::app()->params['envSuffix'];
		$city = Yii::app()->user->city();
		$sql1 = "select a.id,a.start_dt, a.email_text, a.remarks    
				from acc_invoice_email a 
				where a.id>0 
			";
		$sql2 = "select count(a.id)
				from acc_invoice_email a 
				where a.id>0 
			";
		$clause = "";
		if (!empty($this->searchField) && !empty($this->searchValue)) {
			$svalue = str_replace("'","\'",$this->searchValue);
			switch ($this->searchField) {
				case 'start_dt':
					$clause .= General::getSqlConditionClause('a.start_dt',$svalue);
					break;
				case 'email_text':
					$clause .= General::getSqlConditionClause('a.email_text',$svalue);
					break;
			}
		}
		
		$order = "";
		if (!empty($this->orderField)) {
			$order .= " order by ".$this->orderField." ";
			if ($this->orderType=='D') $order .= "desc ";
		}else{
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
				$this->attr[] = array(
					'id'=>$record['id'],
					'start_dt'=>$record['start_dt'],
					'email_text'=>$record['email_text'],
					'remarks'=>$record['remarks'],
				);
			}
		}
		$session = Yii::app()->session;
		$session['invoiceEmail_02'] = $this->getCriteria();
		return true;
	}

}
