<?php
/* Reimbursement Form */

class Report02Form extends CReportForm
{
	public function init() {
		$this->id = 'RptTransList';
		$this->name = Yii::t('report','Transaction List');
		$this->format = 'EXCEL';
		$this->city = Yii::app()->user->city();
		$this->fields = 'start_dt,end_dt';
		$this->end_dt = date("Y/m/d");
		$this->start_dt = date("Y", strtotime($this->end_dt)).'/'.date("m", strtotime($this->end_dt)).'/01';
	}
}
