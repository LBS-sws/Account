<?php
/* Reimbursement Form */

class Report02Form extends CReportForm
{
	public $trans_cat;
	public $acct_id;
	
	public function init() {
		$this->id = 'RptTransList';
		$this->name = Yii::t('report','Transaction List');
		$this->format = 'EXCEL';
		$this->city = Yii::app()->user->city();
		$this->fields = 'start_dt,end_dt';
		$this->end_dt = date("Y/m/d");
		$this->start_dt = date("Y", strtotime($this->end_dt)).'/'.date("m", strtotime($this->end_dt)).'/01';
		$this->trans_cat = 'ALL';
		$this->acct_id = 0;
	}
	
	protected function labelsEx() {
		return array(
					'acct_id'=>Yii::t('trans','Account'),
					'trans_cat'=>Yii::t('code','Type'),
				);
	}

	protected function rulesEx() {
		return array(
					array('acct_id, trans_cat', 'safe'),
				);
	}
	
	protected function queueItemEx() {
		return array(
					'TRANS_CAT'=>$this->trans_cat,
					'ACCT_ID'=>$this->acct_id,
				);
	}

}
